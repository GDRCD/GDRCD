<?php /*HELP: */
/** * Raccolta statistiche sito */
$site_activity = gdrcd_query("SELECT MIN(data_iscrizione) AS date_of_activity FROM personaggio");
$registered_users = gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio");
$banned_users = gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio WHERE esilio > NOW()");
$master_users = gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio WHERE permessi = ".GAMEMASTER);
$admin_users = gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio WHERE permessi >= ".MODERATOR);
$weekly_posts = gdrcd_query("SELECT COUNT(id_messaggio) AS num FROM messaggioaraldo WHERE data_messaggio > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$weekly_actions = gdrcd_query("SELECT COUNT(id) AS num FROM chat WHERE ora > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$weekly_signup = gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio WHERE data_iscrizione > DATE_SUB(NOW(), INTERVAL 7 DAY)");
?>
<strong><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['page_name']); ?></strong>
<table class="statistics">
    <tbody>
    <tr>
        <td class="label"><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['creation_date']); ?>:
        </td>
        <td><?php echo gdrcd_format_date($site_activity['date_of_activity']); ?></td>
    </tr>
    <tr class="pair">
        <td class="label"><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['characters']); ?>:</td>
        <td><?php echo $registered_users['num']; ?></td>
    </tr>
    <tr>
        <td class="label"><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['exiled']); ?>:</td>
        <td><?php echo $banned_users['num']; ?></td>
    </tr>
    <tr class="pair">
        <td class="label"><?php echo gdrcd_filter('out', $PARAMETERS['names']['master']['plur']); ?>:</td>
        <td><?php echo $master_users['num']; ?></td>
    </tr>
    <tr>
        <td class="label"><?php echo gdrcd_filter('out', $PARAMETERS['names']['moderators']['plur']); ?>:</td>
        <td><?php echo $admin_users['num']; ?></td>
    </tr>
    <tr class="pair">
        <td class="label"><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['topics']); ?>:</td>
        <td><?php echo $weekly_posts['num']; ?></td>
    </tr>
    <tr>
        <td class="label"><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['last_chat']); ?>:</td>
        <td><?php echo $weekly_actions    ['num']; ?></td>
    </tr>
    <tr class="pair">
        <td class="label"><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['last_characters']); ?>
            :
        </td>
        <td><?php echo $weekly_signup['num']; ?></td>
    </tr>
    </tbody>
</table>
					