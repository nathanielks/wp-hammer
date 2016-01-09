<?php

class SettingsTest extends WP_SweepTestCase {
    protected $settings;

    public function setUp() {
        $args = array(
            "-l",
            "users=5,posts=100.post_date",
            "-f",
            "posts.post_author=random,users.user_pass=auto,users.user_email=ivan+__ID__@kruchkoff.com,posts.post_title=ipsum",
        );
        $assoc_args = array(
            "dry-run" => "true",
        );
        $this->settings = new WP_CLI\Sweep\Settings();
        $this->settings->parse_arguments( $args, $assoc_args );
    }

    public function testDryRun() {
        $this->assertTrue( $this->settings->dry_run );
    }

    public function testFormats() {
        $this->assertEquals( 4, count( $this->settings->formats ), 'Valid Format Count' );
        $this->assertEquals( 'posts.post_author=random', $this->settings->formats[0], 'Valid table.column=type parse');
        $this->assertEquals( 'users.user_email=ivan+__ID__@kruchkoff.com', $this->settings->formats[2], 'Valid users.user_email=email@format parse' );
    }

    public function testLimits() {
        $this->assertEquals( 2, count( $this->settings->limits ), 'Valid Limit Count' );
        $this->assertEquals( 'users=5', $this->settings->limits[0], 'Valid table=limit parse');
        $this->assertEquals( 'posts=100.post_date', $this->settings->limits[1], 'Valid table=limit.column parse');
    }
}
