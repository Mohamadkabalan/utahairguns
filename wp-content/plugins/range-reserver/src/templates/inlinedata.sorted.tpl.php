<script>
    window.rrData = {};
    var rr = window.rrData;
    rr.Locations = <?php echo $this->models->get_pre_cache_json('rr_locations', $this->models->get_order_by_part('rr_locations')); ?>;
    rr.Bays = <?php echo $this->models->get_pre_cache_json('rr_bays', $this->models->get_order_by_part('rr_bays')); ?>;
    rr.Lanes = <?php echo $this->models->get_pre_cache_json('rr_lanes', $this->models->get_order_by_part('rr_lanes')); ?>;
    rr.MetaFields = <?php echo $this->models->get_pre_cache_json('rr_meta_fields', array('position' => 'ASC')); ?>;
    rr.Status = <?php echo json_encode($this->logic->getStatus()); ?>
</script>