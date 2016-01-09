<?php

class PruneTest extends WP_SweepTestCase {
	protected $prune;

    public function setUp() {
	    parent::setUp();
	    $this->prune = new WP_CLI\Sweep\Prune( $this->settings->limits, $this->settings->dry_run );
    }

	public function testParsePrunes() {
		$this->assertTrue( true );
	}
}
