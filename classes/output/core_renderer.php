<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace theme_ishine\output;

use coding_exception;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use single_select;
use paging_bar;
use url_select;
use context_course;
use pix_icon;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_ishine
 * @copyright  2017 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \theme_boost\output\core_renderer {


    
     public function footer() {
		global $CFG, $DB, $PAGE;

		require_once($CFG->dirroot.'/blocks/navbuttons/footer.php');  // Add this line to enable the navigation buttons
		$output = draw_navbuttons().$this->container_end_all(true);   // Change this line to enable the navigation buttons

		//$output = $this->container_end_all(true);

		$footer = $this->opencontainers->pop('header/footer');

		if (debugging() and $DB and $DB->is_transaction_started()) {
			// TODO: MDL-20625 print warning - transaction will be rolled back
		}

		// Provide some performance info if required
		$performanceinfo = '';
		if (defined('MDL_PERF') || (!empty($CFG->perfdebug) and $CFG->perfdebug > 7)) {
			$perf = get_performance_info();
			if (defined('MDL_PERFTOFOOT') || debugging() || $CFG->perfdebug > 7) {
				$performanceinfo = $perf['html'];
			}
		}

		// We always want performance data when running a performance test, even if the user is redirected to another page.
		if (MDL_PERF_TEST && strpos($footer, $this->unique_performance_info_token) === false) {
			$footer = $this->unique_performance_info_token . $footer;
		}
		$footer = str_replace($this->unique_performance_info_token, $performanceinfo, $footer);

		// Only show notifications when we have a $PAGE context id.
		if (!empty($PAGE->context->id)) {
			$this->page->requires->js_call_amd('core/notification', 'init', array(
				$PAGE->context->id,
				\core\notification::fetch_as_array($this)
			));
		}
		$footer = str_replace($this->unique_end_html_token, $this->page->requires->get_end_code(), $footer);

		$this->page->set_state(\moodle_page::STATE_DONE);

		return $output . $footer;
	}

    public function render_ishineemail_login_signup_form($form) {
                global $SITE;

         $context = $form->export_for_template($this);
         $url = $this->get_logo_url();
         if ($url) {
                        $url = $url->out(false);
                   }
         $context['logourl'] = $url;
         $context['sitename'] = format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID), "escape" => false]);

         return $this->render_from_template('core/signup_form_layout', $context);
     }

    
    

}
