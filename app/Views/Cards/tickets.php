<?php

/**
 * Helper class for this view.
 * TODO: move this helper to own structure
 */
class CardsHelper {

	protected $knownEpics = array();
	
	public function getEpicNumber($epicname) {
		if(empty($epicname)) return "";
		$array_pos = array_search( $epicname, $this->knownEpics );
		if( $array_pos === false ) {
			$this->knownEpics[] = $epicname;
			$array_pos = array_search( $epicname, $this->knownEpics );
		}
		return $array_pos;
	}

}

$helper = new CardsHelper();
?>

<?php foreach( $tickets as $ticket ) { ?>
<div class="ticket">
	<div class="priority <?php echo strtolower($ticket['priority']) ?>"></div>
	<div class="issuetype <?php echo str_replace(' ', '', strtolower($ticket['issuetype'])) ?>"></div>
	<?php if( isset($ticket['epic']) ) { ?>
	<div class="epic epicgroup_<?php echo $helper->getEpicNumber($ticket['epic']); ?>"><?php echo $ticket["epic"] ?></div>
	<?php } ?>
	<div class="number"><?php echo $ticket["key"] ?></div>
	<div class="summary"><?php echo $ticket["summary"] ?></div>
	<?php if( isset($ticket['rank']) ) { ?>
	<div class="rank"><?php echo $ticket["rank"] ?></div>
	<?php } ?>
	<div class="reporter"><?php echo $ticket["reporter"] ?></div>
	<div class="assignee"><?php echo $ticket["assignee"] ?></div>
	<div class="remaining_time"><?php echo $ticket["remaining_time"] ?></div>
</div>
<?php } ?>
