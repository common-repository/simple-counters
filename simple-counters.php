<?php
/*
Plugin Name: Simple Counters
Plugin URI: http://www.simplelib.com/?p=256
Description: Adds simple counters badge (FeedBurner subscribers and Twitter followers) to your blog. Visit <a href="http://www.simplelib.com/">SimpleLib blog</a> for more details.
Version: 2.0.24
Author: minimus
Author URI: http://blogcoding.ru/
*/

/*  Copyright 2009, minimus  (email : minimus@blogcoding.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include_once('sc.class.php');

define('SC_MAIN_FILE', __FILE__);

if(is_admin()) {
  include_once('sc.admin.class.php');
  if (class_exists("SimpleCountersAdmin")) {
	  $minimus_simple_counters = new SimpleCountersAdmin();
  }
}
else {
  if (class_exists("SimpleCounters")) {
    $minimus_simple_counters = new SimpleCounters();
  }
}
?>
