<div class="conversation_members_list">
    <div class="general_title">Lista membri</div>
    <div class="conversation_members_body">
        {foreach $members as $member}
            <div class="single_member">
                <div class="member_avatar">
                    <img src="{$member.url_img_chat}" alt="{$member.nome}"/>
                </div>
                <div class="member_name">
                    <a href="/main.php?page=scheda/index&id_pg={$member.pg_id}">{$member.nome} {$member.cognome}</a>
                </div>
            </div>
        {/foreach}
    </div>
</div>