<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Mittagsplaner</title>
<style type="text/css">
body { background-color:white; font-family:sans; text-align:center; }
th { width:100px; padding:5px; border-width:0px; width:100px; font-size:large; background-color:#BEBEBE; color:white; }
td { padding:5px; background-color:#F0F0F0; color:black; }
th a { font-size:small; font-weight:normal; color:white; }
td.new { background-color:#BEBEBE; color:black; }
td.clear { background-color:white; padding:0px; color:black; }
</style>
</head>
<body>
<?php
    // set constants:
    $weekdays = array( 1 => "Montag", 2 => "Dienstag", 3 => "Mittwoch", 4 => "Donnerstag", 5 => "Freitag" );
    $verb = array( 'walk' => 'geht zu Fu&szlig;', 'wheel' => 'f&auml;hrt mit dem Auto', 'seat' => 'm&ouml;chte mitgenommen werden', 'bike' => 'f&auml;hrt mit dem Rad' );
    $temp_dir = '/tmp';
    $filename_places = $temp_dir . DIRECTORY_SEPARATOR . 'lunchplannerplaces.json';
    $filebase_entries = $temp_dir . DIRECTORY_SEPARATOR . 'lunchplannerentries';
    setlocale(LC_ALL, 'de_DE');
    
    // reset places:
    // file_put_contents($filename_places, '{"3b3671461dedf2929614546d22df85df":{"name":"Mensa","prep":"in die","url":"http://www.studentenwerk-s-h.de/seiten_essen/plan_mensa_gaarden.html"},"43cdbd4b26560bcd7124ba100d5a3429":{"name":"Antep Sofrasi","prep":"zu","url":""},"d7e9c27d4cf74d00e41f209e06fba8af":{"name":"Arbeitsamt","prep":"zum","url":"http://kiel-kantine.de/index.php/Arbeitsamt.html"},"ca2c4deaa57f6cddd29e0695efbd5846":{"name":"Sozial-Ministerium","prep":"zum","url":"http://kiel-kantine.de/index.php/Ministerium_f.Arbeit.html"},"4264601391ce362b19f5d9094ba5f2d8":{"name":"Falafel Baban","prep":"zu","url":""},"1eea0954e689af0837e6cf5dcbff6346":{"name":"Prego"},"2292559dd81405527195b83a4610b6f7":{"name":"Agora"}}');
    
    // today:
    $day = new DateTime();
    $beta_img = 'beta.png';
    if ($day->format('y') == 12 and ($day->format('m') == 11 or $day->format('m') == 11)) { $beta_img = 'a0.png'; }
    if ($day->format('y') == 12 and $day->format('m') == 12) {
        if ($day->format('d') >= 2) { $beta_img = 'a1.png'; }
        if ($day->format('d') >= 9) { $beta_img = 'a2.png'; }
        if ($day->format('d') >= 16) { $beta_img = 'a3.png'; }
        if ($day->format('d') >= 23) { $beta_img = 'a4.png'; }
    }
    
    // get 'current' day:
    if ($day->format('H') > 12) { $day->modify('+1 day'); }
    while ($day->format('N') > 5) { $day->modify('+1 day'); }
    
    // get current filename:
    $filename_entries = $filebase_entries . $day->format('ymd') . '.json';

    // delete old files:
    if (!file_exists($filename_entries)) {
        foreach (glob($filebase_entries . '*') as $stale_file) { unlink($stale_file); }
        file_put_contents($filename_entries, json_encode(array()));
    }
    
    // load places, more places and entries:
    $places = json_decode(file_get_contents($filename_places), TRUE);
    $entries = json_decode(file_get_contents($filename_entries), TRUE);
    
    // delete entry:
    foreach (array_keys($_POST) as $delete_flag) {
        if (substr($delete_flag, 0, 13) == 'delete_entry_') {
            unset($entries[substr($delete_flag, 13, -2)]);
            file_put_contents($filename_entries, json_encode($entries));
        }
    }

    $clean_name = utf8_encode(htmlspecialchars($_POST['name']));
    $name_hash = hash('md5', $clean_name);
    
    // add entry:
    if (array_key_exists('add_entry_x', $_POST) and !array_key_exists($name_hash, $entries)) {
        $entries[$name_hash] = array('name' => $clean_name, 'choice' => $_POST['choice'], 'vehicle' => $_POST['vehicle']);
        file_put_contents($filename_entries, json_encode($entries));
    }
    
    // delete place:
    foreach (array_keys($_POST) as $delete_flag) {
        if (substr($delete_flag, 0, 13) == 'delete_place_') {
            unset($places[substr($delete_flag, 13, -2)]);
            file_put_contents($filename_places, json_encode($places));
        }
    }

    // add place:
    if (array_key_exists('add_place_x', $_POST) and !array_key_exists($name_hash, $places)) {
       $places[$name_hash] = array('name' => $clean_name);
       file_put_contents($filename_places, json_encode($places));
    }
?>
    <form action="" method="post">
        <h1 style="color:#A0A0A0;">Mittagsplaner</h1>
        <h2 style="color:#A0A0A0;"><?php echo $weekdays[$day->format('N')] . ', ' . $day->format('d.m.Y') ?></h2>
        <div>
            <table style="margin-left:auto; margin-right:auto;">
<?php
// ======== names of places ===================================================
?>
                <tr style="text-align:center;">
                    <td class="clear" colspan="2"></td>
<?php foreach ($places as $place) { ?>
                    <th><?php echo utf8_decode($place['name']); ?></th>
<?php } if (array_key_exists('new_place_x', $_POST)) { ?>
                    <td class="new">
                        <input type="text" name="name" value="" tabindex=1>
                    </td>
                    <td class="clear">
                        <input type="image" src="accept.png" name="add_place" alt="&#x2713" title="&Uuml;bernehmen" tabindex=3>
                        <input type="image" src="delete.png" name="discard_place" alt="&#x2717" title="Abbrechen" tabindex=4>
                    </td>
<?php } elseif (!array_key_exists('new_entry_x', $_POST)) { ?>
                    <td class="clear">
                        <input type="image" name="new_place" src="add.png" alt="+" value="+" title="Ort hinzuf&uuml;gen" tabindex=1>
                    </td>
<?php } ?>
                </tr>
<?php
// ======== urls and delete buttons ===========================================
?>
                <tr style="text-align:center;">
                    <td class="clear" colspan="2"></td>
<?php
foreach ($places as $place_key => $place) {
    if (array_key_exists('url', $place)) {
        if ($place['url'] == '') {
            $url_field = '&#x00A0';
        } else {
            $url_field = "<a href=\"{$place['url']}\">Angebot</a>";
        }
    } else {
        if (array_key_exists('new_entry_x', $_POST)) {
            $url_field = '&#x00A0';
        } else {
            $url_field = "<input type=\"image\" name=\"delete_place_{$place_key}\" src=\"delete.png\" alt=\"&#x2717\" value=\"&#x2717\" title=\"{$place['name']} entfernen\">";
        }
    }
?>
                    <th><?php echo $url_field ?></th>
<?php
}
?>
                </tr>
<?php
// ======== entries ===========================================================

foreach ($entries as $entry_key => $entry) {
    $entry_icon = $entry['vehicle'] . '-dark.png';
    if (array_key_exists('prep', $places[$entry['choice']])) {$entry_prep = $places[$entry['choice']]['prep']; } else {$entry_prep = 'zu'; }
    if ($entry['vehicle'] == 'chef') {
        $entry_text = utf8_decode($entry['name']) . ' versorgt sich selbst';
    } else {
        $entry_text = utf8_decode($entry['name']) . ' ' . $verb[$entry['vehicle']] . ' ' . $entry_prep . ' ' . $places[$entry['choice']]['name'];
    }
?>
                <tr align="center" class="entry" title="<?php echo $entry_text; ?>">
                    <td align="left" style="width:100px;"><?php echo utf8_decode($entry['name']) ?></td>
                    <td style="width:0px;">
                        <img src="<?php echo $entry_icon; ?>" alt="">
                    </td>
<?php
    foreach ($places as $place_key => $place) {
        if ($place_key == $entry['choice'] and $entry['vehicle'] != 'chef') {
?>
                    <td><img src="accept.png" alt="&#x2713"></td>
<?php
        } else {
?>
                    <td>&#x00A0</td>
<?php
        }
    }
    if (!array_key_exists('new_entry_x', $_POST) and !array_key_exists('new_place', $_POST)) {
?>
                    <td class="clear">
                        <input type="image" name="delete_entry_<?php echo $entry_key; ?>" src="delete.png" alt="&#x2717" title="<?php echo $entry['name']; ?> entfernen">
                    </td>
<?php
    }
?>
                </tr>
<?php
}
// ======== new entry =========================================================

if (array_key_exists('new_entry_x', $_POST)) {
?>
                <tr align="center" class="new">
                    <td class="new" colspan="2">
                        <input type="text" name="name" value="" tabindex=1>
                        <p>
                            <input type="radio" name="vehicle" value="walk" title="zu Fu&szlig;" checked><img src="walk-white.png" title="zu Fu&szlig;">&#x00A0
                            <input type="radio" name="vehicle" value="wheel" title="mit dem Auto"><img src="wheel-white.png" title="mit dem Auto">&#x00A0
                            <input type="radio" name="vehicle" value="seat" title="als Mitfahrer"><img src="seat-white.png" title="als Mitfahrer">&#x00A0
                            <input type="radio" name="vehicle" value="bike" title="mit dem Rad"><img src="bike-white.png" title="mit dem Rad">&#x00A0
                            <input type="radio" name="vehicle" value="chef" title="Selbstversorger"><img src="chef-white.png" title="Selbstversorger">
                        </p>
                    </td>
<?php
    foreach ($places as $place_key => $place) {
?>
                    <td class="new">
                        <input type="radio" name="choice" value="<?php echo $place_key; ?>" tabindex="2">
                    </td>
<?php
    }
    foreach ($more_places as $place_key => $place) {
?>
                    <td class="new">
                        <input type="radio" name="choice" value="<?php echo $place_key; ?>" tabindex="2">
                    </td>
<?php
    }
?>
                    <td class="clear">
                        <input type="image" name="add_entry" src="accept.png" alt="&#x2713" title="&Uuml;bernehmen" tabindex=3>
                        <input type="image" name="discard_entry" src="delete.png" alt="&#x2713" title="Abbrechen" tabindex=4>
                    </td>
                </tr>
<?php
} elseif (!array_key_exists('new_place_x', $_POST)) {
?>
                <tr align="center">
                    <td class="clear"><input type="image" name="new_entry" src="add.png" alt="+" title="Eintrag hinzuf&uuml;gen" tabindex=1></td>
                </tr>
<?php
}
?>
            </table>
        </div>
    </form>
    <div style="position:fixed; top:0px; right:0px;">
        <img src="<?php echo $beta_img; ?>" alt="BETA">
    </div>
</body>
</html>
