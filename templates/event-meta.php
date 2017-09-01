<?php

$date_start = new \DateTime($event_fields['date_start']);
$start_day = $date_start->format('d');
$start_month = $date_start->format('M');

if (!empty($event_fields['date_end'])) {
  $date_end = $event_fields['date_end'];
}

$event_fields['time_start'] = ltrim($event_fields['time_start'], '0');
$event_fields['time_end'] = ltrim($event_fields['time_end'], '0');

$start_html =  '<time>'.$event_fields['date_start'].', '.$event_fields['time_start'];
if (empty($event_fields['date_end']) && !empty($event_fields['time_end'])) {
  $start_html .= ' - '.$event_fields['time_end'];
}
$start_html .= '</time>';

if (!empty($event_fields['date_end'])) {
  $start_header = 'Start';
}
else {
  $start_header = 'Date/Time';
}
?>
<div class="meta">
  <strong><?= $start_header; ?></strong>
  <p>
    <?= $start_html; ?>
  </p>

  <?php if (!empty($event_fields['date_end'])) : ?>
  <strong>End</strong>
  <p>
    <?= '<time>'.$event_fields['date_end'].', '.$event_fields['time_end'].'</time>'; ?>
  </p>
  <?php endif; ?>

  <strong>Location</strong>
  <p>
    <?php
    if (!empty($event_fields['location'])) :
      if (!empty($event_fields['location_name'])) :
        echo $event_fields['location_name'].'<br/>';
      endif;
      
      echo $event_fields['location']['address'];
      ?>
      <div class="acf-map">
      	<div class="marker" data-lat="<?php echo $event_fields['location']['lat']; ?>" data-lng="<?php echo $event_fields['location']['lng']; ?>"></div>
      </div>
      <?php
    else:
      echo "No location set.";
    endif;
    ?>
  </p>

  <hr/>
</div>
