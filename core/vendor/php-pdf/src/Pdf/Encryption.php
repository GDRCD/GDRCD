<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * PDF Standard Security Handler.
 *
 * ISO 32000-1 §7.6, Algorithms 2-7; Adobe Supplement to ISO 32000 §3.5.
 *
 * Supported:
 *  - RC4_128: V=2, R=3, Length=128 (RC4-128 stream cipher).
 *  - AES_128: V=4, R=4, Length=128 + CFM AESV2 (AES-128-CBC).
 *  - AES_256: V=5, R=5, Length=256 + CFM AESV3 (AES-256-CBC).
 *  - AES_256 R6: ISO 32000-2 Algorithm 2.B iterative hash (PDF 2.0).
 *  - User password (for opening); owner falls back to user if omitted.
 *  - Permissions bits (printing, copying, modification, ...).
 *
 * Not implemented:
 *  - Public-key encryption (/Filter /PubSec).
 */
final class Encryption
{
    /** Standard PDF password padding (ISO 32000-1 §7.6.3.3 Algorithm 2 step a). */
    private const PADDING = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";

    /** Default permissions: print + copy + modify + annotations + assemble. */
    public const PERM_PRINT = 4;

    public const PERM_MODIFY = 8;

    public const PERM_COPY = 16;

    public const PERM_ANNOTATE = 32;

    public const PERM_FILL_FORMS = 256;

    public const PERM_ACCESSIBILITY = 512;

    public const PERM_ASSEMBLE = 1024;

    public const PERM_PRINT_HIGH = 2048;

    /** Reserved bits set per spec (bits 7,8,13..32) — set to 1 always. */
    private const RESERVED_BITS = 0xFFFFF0C0;

    public readonly string $oValue;

    public readonly string $uValue;

    /** 16-byte file encryption key. */
    public readonly string $fileKey;

    public readonly int $permissions;

    public readonly string $fileId;

    public readonly EncryptionAlgorithm $algorithm;

    /** AES-256 V5 R5/R6 additional fields. */
    public readonly string $oeValue;

    public readonly string $ueValue;

    public readonly string $permsValue;

    public function __construct(
        string $userPassword,
        ?string $ownerPassword = null,
        int $permissions = self::PERM_PRINT | self::PERM_COPY | self::PERM_PRINT_HIGH,
        EncryptionAlgorithm $algorithm = EncryptionAlgorithm::Rc4_128,
    ) {
        $this->algorithm = $algorithm;
        if ($algorithm === EncryptionAlgorithm::Aes_128 && ! self::aesAvailable()) {
            throw new \RuntimeException('AES-128 encryption requires openssl extension with aes-128-cbc support');
        }
        if ($algorithm === EncryptionAlgorithm::Aes_256 && ! self::aes256Available()) {
            throw new \RuntimeException('AES-256 encryption requires openssl extension with aes-256-cbc support');
        }
        if ($algorithm === EncryptionAlgorithm::Aes_256_R6 && ! self::aes256R6Available()) {
            throw new \RuntimeException('AES-256 R6 encryption requires openssl extension with aes-128-cbc + aes-256-cbc + aes-256-ecb support');
        }

        // V5 R5 path — completely different key/O/U derivation.
        if ($algorithm === EncryptionAlgorithm::Aes_256) {
            $this->initAes256($userPassword, $ownerPassword, $permissions, useR6Hash: false);

            return;
        }
        // V5 R6 (PDF 2.0) — same dictionary layout, iterative Algorithm 2.B
        // hash used instead of a single SHA-256 call.
        if ($algorithm === EncryptionAlgorithm::Aes_256_R6) {
            $this->initAes256($userPassword, $ownerPassword, $permissions, useR6Hash: true);

            return;
        }
        // Defaults for V5-only fields when using V2/V4.
        $this->oeValue = '';
        $this->ueValue = '';
        $this->permsValue = '';
        $owner = $ownerPassword ?? $userPassword;
        $userPadded = self::padPassword($userPassword);
        $ownerPadded = self::padPassword($owner);

        // Permissions: combine with reserved bits; signed 32-bit.
        $p = ($permissions | self::RESERVED_BITS) & 0xFFFFFFFF;
        // Convert to signed for PDF /P entry.
        if ($p >= 0x80000000) {
            $p -= 0x100000000;
        }
        $this->permissions = $p;

        // File ID — 16 random bytes.
        $this->fileId = random_bytes(16);

        // Algorithm 3: compute /O value.
        $this->oValue = self::computeOValue($ownerPadded, $userPadded);

        // Algorithm 2: compute file encryption key.
        $this->fileKey = self::computeFileKey($userPadded, $this->oValue, $this->permissions, $this->fileId);

        // Algorithm 5: compute /U value.
        $this->uValue = self::computeUValue($this->fileKey, $this->fileId);
    }

    /**
     * Encrypt data for PDF object N generation G (typically G=0).
     *
     * RC4_128: Algorithm 1 — RC4 stream cipher with a per-object key.
     * AES_128: Algorithm 1.b — AES-128-CBC with per-object key + random
     *          IV, IV prepended to the ciphertext.
     * AES_256 (V5 R5/R6): uses the fileKey directly + random IV.
     */
    public function encryptObject(string $data, int $objNum, int $genNum = 0): string
    {
        // V5 R5+R6 use fileKey directly + random IV — no per-object
        // derivation. Stream encryption is identical between R5 and R6.
        if ($this->algorithm === EncryptionAlgorithm::Aes_256
            || $this->algorithm === EncryptionAlgorithm::Aes_256_R6) {
            $iv = random_bytes(16);
            $cipher = (string) openssl_encrypt(
                $data,
                'aes-256-cbc',
                $this->fileKey,
                OPENSSL_RAW_DATA,
                $iv,
            );

            return $iv . $cipher;
        }

        // Build per-object key base: fileKey + obj_num (3 bytes LE) + gen_num (2 bytes LE).
        $base = $this->fileKey
            . chr($objNum & 0xFF)
            . chr(($objNum >> 8) & 0xFF)
            . chr(($objNum >> 16) & 0xFF)
            . chr($genNum & 0xFF)
            . chr(($genNum >> 8) & 0xFF);

        if ($this->algorithm === EncryptionAlgorithm::Aes_128) {
            // AES per-object key: salt "sAlT" appended before MD5.
            $key = substr(md5($base . "sAlT", true), 0, min(16, strlen($this->fileKey) + 5));
            $iv = random_bytes(16);
            $cipher = (string) openssl_encrypt(
                $data,
                'aes-128-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv,
            );

            return $iv . $cipher;
        }

        $objKey = substr(md5($base, true), 0, min(16, strlen($this->fileKey) + 5));

        return self::rc4($objKey, $data);
    }

    public static function aesAvailable(): bool
    {
        return function_exists('openssl_encrypt')
            && in_array('aes-128-cbc', openssl_get_cipher_methods(), true);
    }

    public static function aes256Available(): bool
    {
        return function_exists('openssl_encrypt')
            && in_array('aes-256-cbc', openssl_get_cipher_methods(), true)
            && in_array('aes-256-ecb', openssl_get_cipher_methods(), true);
    }

    /** R6 hash needs AES-128-CBC in addition to AES-256. */
    public static function aes256R6Available(): bool
    {
        return self::aes256Available()
            && in_array('aes-128-cbc', openssl_get_cipher_methods(), true);
    }

    /**
     * Initialize V5 R5 (Adobe Supplement) or R6 (PDF 2.0) AES-256 encryption.
     *
     * Algorithm overview:
     *  - User pw: not padded; UTF-8 bytes truncated to 127.
     *  - Generate 8-byte user validation salt + 8-byte user key salt.
     *  - userHash = SHA-256(pw + userValidationSalt). /U = userHash || saltA || saltB (48 bytes).
     *  - userIntermediateKey = SHA-256(pw + userKeySalt). AES-256-CBC(userIntermediateKey, IV=0)
     *    encrypts fileEncryptionKey → /UE.
     *  - Similar for owner pw, but the /U value is mixed into the hash inputs.
     *  - /Perms = AES-256-ECB(extendedPermBytes, fileEncryptionKey).
     */
    private function initAes256(string $userPassword, ?string $ownerPassword, int $permissions, bool $useR6Hash): void
    {
        $owner = $ownerPassword ?? $userPassword;
        $userBytes = substr($userPassword, 0, 127);
        $ownerBytes = substr($owner, 0, 127);

        // Generate random salts + file encryption key.
        $userValidationSalt = random_bytes(8);
        $userKeySalt = random_bytes(8);
        $ownerValidationSalt = random_bytes(8);
        $ownerKeySalt = random_bytes(8);
        $fileEncryptionKey = random_bytes(32);

        // Permissions sign-extended.
        $p = ($permissions | self::RESERVED_BITS) & 0xFFFFFFFF;
        if ($p >= 0x80000000) {
            $p -= 0x100000000;
        }
        // Direct assignment is valid for a readonly + uninitialized field
        // inside the constructor.
        $this->permissions = $p;
        $this->fileId = random_bytes(16);

        // R6 replaces the single SHA-256 with iterative Algorithm 2.B.
        $hashFn = $useR6Hash
            ? fn (string $pw, string $salt, string $udata = ''): string => self::computeR6Hash($pw, $salt, $udata)
            : fn (string $pw, string $salt, string $udata = ''): string => hash('sha256', $pw . $salt . $udata, true);

        // /U entry: hash(pw + valSalt) || valSalt || keySalt → 48 bytes.
        $userHash = $hashFn($userBytes, $userValidationSalt);
        $this->uValue = $userHash . $userValidationSalt . $userKeySalt;

        // /O entry: hash(pw + valSalt + U[0..48]) || valSalt || keySalt.
        // U in the hash input is the 48-byte /U value computed above.
        $ownerHash = $hashFn($ownerBytes, $ownerValidationSalt, $this->uValue);
        $this->oValue = $ownerHash . $ownerValidationSalt . $ownerKeySalt;

        // /UE: AES-256-CBC(key=hash(pw + userKeySalt), IV=zeros, fileKey).
        $userInterKey = $hashFn($userBytes, $userKeySalt);
        $this->ueValue = self::aes256CbcNoPadding(
            $userInterKey, str_repeat("\x00", 16), $fileEncryptionKey,
        );

        // /OE: similar with owner + U.
        $ownerInterKey = $hashFn($ownerBytes, $ownerKeySalt, $this->uValue);
        $this->oeValue = self::aes256CbcNoPadding(
            $ownerInterKey, str_repeat("\x00", 16), $fileEncryptionKey,
        );

        // /Perms: AES-256-ECB(permsExtended, fileKey).
        // Layout: 4 bytes signed perms (LE) + 0xFFFFFFFF + 'T' (encrypt meta) +
        //         'adb' (magic) + 4 random bytes = 16 bytes.
        $permsExtended = pack('V', $p < 0 ? $p + 0x100000000 : $p)
            . "\xFF\xFF\xFF\xFF"
            . 'T'
            . 'adb'
            . random_bytes(4);
        $this->permsValue = (string) openssl_encrypt(
            $permsExtended,
            'aes-256-ecb',
            $fileEncryptionKey,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
        );

        $this->fileKey = $fileEncryptionKey;
    }

    /**
     * AES-256-CBC without padding (key 32, iv 16, plaintext must be a
     * 16-byte multiple).
     */
    private static function aes256CbcNoPadding(string $key, string $iv, string $data): string
    {
        return (string) openssl_encrypt(
            $data,
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $iv,
        );
    }

    /**
     * Algorithm 5 step (a): pad password to 32 bytes.
     */
    private static function padPassword(string $password): string
    {
        $bytes = substr($password, 0, 32);
        if (strlen($bytes) < 32) {
            $bytes .= substr(self::PADDING, 0, 32 - strlen($bytes));
        }

        return $bytes;
    }

    /**
     * Algorithm 3: compute /O entry.
     */
    private static function computeOValue(string $ownerPadded, string $userPadded): string
    {
        // a. MD5 of owner_padded.
        $hash = md5($ownerPadded, true);
        // b. Iterate MD5 50 times (V≥2, R≥3).
        for ($i = 0; $i < 50; $i++) {
            $hash = md5($hash, true);
        }
        // c. Take first 16 bytes as RC4 key.
        $rc4Key = substr($hash, 0, 16);
        // d. Encrypt user_padded with RC4.
        $encrypted = self::rc4($rc4Key, $userPadded);
        // e. Iterate XOR cycling key bytes 19 times.
        for ($i = 1; $i <= 19; $i++) {
            $alt = '';
            for ($j = 0; $j < 16; $j++) {
                $alt .= chr(ord($rc4Key[$j]) ^ $i);
            }
            $encrypted = self::rc4($alt, $encrypted);
        }

        return $encrypted;
    }

    /**
     * Algorithm 2: compute file encryption key.
     */
    private static function computeFileKey(string $userPadded, string $oValue, int $permissions, string $fileId): string
    {
        $data = $userPadded;
        $data .= $oValue;
        // Permissions as 4-byte LE.
        $p = $permissions < 0 ? $permissions + 0x100000000 : $permissions;
        $data .= chr($p & 0xFF) . chr(($p >> 8) & 0xFF)
            . chr(($p >> 16) & 0xFF) . chr(($p >> 24) & 0xFF);
        $data .= $fileId;
        // Step (f): metadata flag — for V4+ only. Skip for V2.

        $hash = md5($data, true);
        // V≥2, R≥3: iterate MD5 50 more times truncated to 16 bytes.
        for ($i = 0; $i < 50; $i++) {
            $hash = md5(substr($hash, 0, 16), true);
        }

        return substr($hash, 0, 16);
    }

    /**
     * Algorithm 5: compute /U entry (R≥3).
     */
    private static function computeUValue(string $fileKey, string $fileId): string
    {
        // MD5 of padding + fileID.
        $hash = md5(self::PADDING . $fileId, true);
        // RC4 encrypt the 16-byte hash with fileKey.
        $encrypted = self::rc4($fileKey, $hash);
        // Iterate 19 rounds with XOR'd key.
        for ($i = 1; $i <= 19; $i++) {
            $alt = '';
            for ($j = 0; $j < 16; $j++) {
                $alt .= chr(ord($fileKey[$j]) ^ $i);
            }
            $encrypted = self::rc4($alt, $encrypted);
        }
        // Pad encrypted (16 bytes) to 32 bytes by appending 16 arbitrary
        // bytes. Spec allows any; many encoders append zeros, others
        // duplicate the first 16. We duplicate the first 16 (commonly seen).
        return $encrypted . substr($encrypted, 0, 16);
    }

    /**
     * RC4 stream cipher. We avoid openssl_encrypt because RC4 was removed
     * from the OpenSSL 3.0 default provider. Self-contained, ~30 lines.
     */
    public static function rc4(string $key, string $data): string
    {
        $s = range(0, 255);
        $keyBytes = array_values(unpack('C*', $key) ?: []);
        $keyLen = count($keyBytes);
        $j = 0;
        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + $keyBytes[$i % $keyLen]) % 256;
            [$s[$i], $s[$j]] = [$s[$j], $s[$i]];
        }
        $result = '';
        $i = 0;
        $j = 0;
        $len = strlen($data);
        for ($k = 0; $k < $len; $k++) {
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            [$s[$i], $s[$j]] = [$s[$j], $s[$i]];
            $kt = $s[($s[$i] + $s[$j]) % 256];
            $result .= chr(ord($data[$k]) ^ $kt);
        }

        return $result;
    }

    /**
     * Encode bytes as a PDF literal string in hex form: <abcdef...>.
     */
    public static function asHexString(string $bytes): string
    {
        return '<' . bin2hex($bytes) . '>';
    }

    /**
     * ISO 32000-2 Algorithm 2.B — R6 iterative hash.
     *
     * Mixes password + intermediate hash + optional U-value through 64+
     * rounds of AES-128-CBC and a SHA-2 variant picked dynamically by
     * `int(E[0..16]) mod 3`. Terminates after round ≥64 when the last
     * byte of E ≤ (round - 32).
     *
     * @param  string  $udata  optional 48-byte U value (for /O hash) or ''.
     */
    public static function computeR6Hash(string $password, string $salt, string $udata = ''): string
    {
        $K = hash('sha256', $password . $salt . $udata, true);
        $round = 0;
        $lastByte = 0;
        while (true) {
            // K1 = (password || K || udata) repeated 64 times.
            $K1 = str_repeat($password . $K . $udata, 64);
            $aesKey = substr($K, 0, 16);
            $iv = substr($K, 16, 16);
            $E = (string) openssl_encrypt(
                $K1,
                'aes-128-cbc',
                $aesKey,
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                $iv,
            );

            $sumMod3 = self::bigIntMod3(substr($E, 0, 16));
            $hashAlg = match ($sumMod3) {
                0 => 'sha256',
                1 => 'sha384',
                default => 'sha512',
            };
            $K = hash($hashAlg, $E, true);

            $lastByte = ord($E[strlen($E) - 1]);
            $round++;
            if ($round >= 64 && $lastByte <= $round - 32) {
                break;
            }
        }

        return substr($K, 0, 32);
    }

    /**
     * Big-endian unsigned integer mod 3, byte-by-byte (no GMP dependency).
     *
     * Iterates acc = (acc * 256 + byte) % 3. Mathematically identical to
     * BigInteger().mod(3) but works on arbitrary-length byte strings.
     */
    private static function bigIntMod3(string $bytes): int
    {
        $mod = 0;
        $len = strlen($bytes);
        for ($i = 0; $i < $len; $i++) {
            $mod = ($mod * 256 + ord($bytes[$i])) % 3;
        }

        return $mod;
    }
}
