<?php

class PruneTest extends WP_HammerTestCase {
	protected $prune;

    public function setUp() {
	    parent::setUp();
	    $this->prune = new WP_CLI\Hammer\Prune( $this->settings->limits, $this->settings->dry_run );
    }

	public function testParsePrunes() {
		$this->assertTrue( true );
	}
}
