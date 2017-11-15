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
defined('MOODLE_INTERNAL') || die();

/* HACKS BY JUSTIN for IShine */
// Be sure to include the original renderer so it can be extended
require_once($CFG->dirroot . '/course/format/topcoll/renderer.php');

/**
 * Class theme_h5pmod_mod_hvp_renderer
 *
 * Extends the H5P renderer so that we are able to override the relevant
 * functions declared there
 */
class format_topcoll_renderer extends \format_topcoll_renderer {
    
    /**
     * Generate the display of the header part of a section before
     * course modules are included.
     *
     * @param stdClass $section The course_section entry from DB.
     * @param stdClass $course The course entry from DB.
     * @param bool $onsectionpage true if being printed on a section page.
     * @param int $sectionreturn The section to return to after an action.
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn = null) {
        $o = '';

        $sectionstyle = '';
        $rightcurrent = '';
        $context = \context_course::instance($course->id);

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if ($this->courseformat->is_section_current($section)) {
                $section->toggle = true; // Open current section regardless of toggle state.
                $sectionstyle = ' current';
                $rightcurrent = ' left';
            }
        }

        if ((!$this->formatresponsive) && ($section->section != 0) &&
            ($this->tcsettings['layoutcolumnorientation'] == 2)) { // Horizontal column layout.
            $sectionstyle .= ' ' . $this->get_column_class($this->tcsettings['layoutcolumns']);
        }
        $liattributes = array(
            'id' => 'section-' . $section->section,
            'class' => 'section main clearfix' . $sectionstyle,
            'role' => 'region',
            'aria-label' => $this->courseformat->get_topcoll_section_name($course, $section, false)
        );
        if (($this->formatresponsive) && ($this->tcsettings['layoutcolumnorientation'] == 2)) { // Horizontal column layout.
            $liattributes['style'] = 'width: ' . $this->tccolumnwidth . '%;';
        }
        $o .= \html_writer::start_tag('li', $liattributes);

        if ((($this->mobiletheme === false) && ($this->tablettheme === false)) || ($this->userisediting)) {
            $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
            $rightcontent = '';
            if (($section->section != 0) && $this->userisediting && has_capability('moodle/course:update', $context)) {
                $url = new \moodle_url('/course/editsection.php', array('id' => $section->id, 'sr' => $sectionreturn));

                $rightcontent .= \html_writer::link($url,
                    \html_writer::empty_tag('img',
                        array('src' => $this->output->pix_url('t/edit'),
                        'class' => 'icon edit tceditsection', 'alt' => get_string('edit'))),
                        array('title' => get_string('editsection', 'format_topcoll'), 'class' => 'tceditsection'));
            }
            $rightcontent .= $this->section_right_content($section, $course, $onsectionpage);

            if ($this->rtl) {
                // Swap content.
                $o .= \html_writer::tag('div', $rightcontent, array('class' => 'right side'));
                $o .= \html_writer::tag('div', $leftcontent, array('class' => 'left side'));
            } else {
                $o .= \html_writer::tag('div', $leftcontent, array('class' => 'left side'));
                $o .= \html_writer::tag('div', $rightcontent, array('class' => 'right side'));
            }
        }
        $o .= \html_writer::start_tag('div', array('class' => 'content'));

        if (($onsectionpage == false) && ($section->section != 0)) {
        
        	//edits Justin to add the $notoggle class to unavailable sections
        	$section_unavailable = $section->visible && !$section->available && !empty($section->availableinfo);
        	$notoggle_class='';
        	$toggleicon_class='toggle-'.$this->tcsettings['toggleiconset'];        	
        	 if ($section_unavailable) {
                        $notoggle_class='notoggle';
                        $toggleicon_class='';
             };
            
        
            $o .= \html_writer::start_tag('div',
                array('class' => 'sectionhead toggle ' . $notoggle_class . ' ' . $toggleicon_class,
                'id' => 'toggle-'.$section->section)
            );

            if ((!($section->toggle === null)) && ($section->toggle == true)) {
                $toggleclass = 'toggle_open';
                $ariapressed = 'true';
                $sectionclass = ' sectionopen';
            } else {
                $toggleclass = 'toggle_closed';
                $ariapressed = 'false';
                $sectionclass = '';
            }
            $toggleclass .= ' the_toggle ' . $this->tctoggleiconsize;
            $o .= \html_writer::start_tag('span',
                array('class' => $toggleclass, 'role' => 'button', 'aria-pressed' => $ariapressed)
            );

            if (empty($this->tcsettings)) {
                $this->tcsettings = $this->courseformat->get_settings();
            }

            if ($this->userisediting) {
                $title = $this->section_title($section, $course);
            } else {
                $title = $this->courseformat->get_topcoll_section_name($course, $section, true);
            }
            if ($this->userisediting) {
                $o .= $this->output->heading($title, 3, 'sectionname');
            } else {
                $o .= \html_writer::tag('h3', $title); // Moodle H3's look bad on mobile / tablet with CT so use plain.
            }
            
            //EDIT JUSTIN add completed check mark if its complete
            $modinfo = get_fast_modinfo($course);
        	$sect_compl_info = \availability_sectioncompleted\condition::get_section_completion_info(
                            $section->section,
                            $course,
                            $modinfo);
            $iscomplete = ($sect_compl_info->sectioncompletedcount==1);
            if($iscomplete){
            	$o .= \html_writer::start_tag('span',
                	array('class' => 'actions_module')
            	);
            	$o .= \html_writer::start_tag('span',
                	array('class' => 'auto_completion')
            	);
            	$o .= \html_writer::empty_tag('img',
                        array('src' =>'https://s3-ap-northeast-1.amazonaws.com/ishinevideocontent99/publiccontent/courses/siteimages/module_clear_check.png',
                        'class' => 'icon smallicon', 'alt' => '完了：' . $title, 'title' => '完了：' . $title)
                );
                $o .= \html_writer::end_tag('span');
                $o .= \html_writer::end_tag('span');
            }
            

            $o .= \html_writer::end_tag('span');
            $o .= \html_writer::end_tag('div');

            //added an availability check to prevent showing summary when its unavailable
            //if ($this->tcsettings['showsectionsummary'] == 2) {
            if ($this->tcsettings['showsectionsummary'] == 2 && !$section_unavailable) {
                $o .= $this->section_summary_container($section);
            }

            $o .= \html_writer::start_tag('div',
                array('class' => 'sectionbody toggledsection' . $sectionclass,
                'id' => 'toggledsection-' . $section->section)
            );

            if ($this->userisediting && has_capability('moodle/course:update', $context)) {
                $url = new \moodle_url('/course/editsection.php', array('id' => $section->id, 'sr' => $sectionreturn));
                $o .= \html_writer::link($url,
                    \html_writer::empty_tag('img',
                        array('src' => $this->output->pix_url('t/edit'),
                        'class' => 'iconsmall edit', 'alt' => get_string('edit'))),
                        array('title' => get_string('editsection', 'format_topcoll'))
                );
            }
			
			//Edit JUSTIN 2017/06/29
			//added an availability check to prevent showing summary when its unavailable
            //if ($this->tcsettings['showsectionsummary'] == 1) {
            if ($this->tcsettings['showsectionsummary'] == 1 && !$section_unavailable) {
                $o .= $this->section_summary_container($section);
            }

			//Edit JUSTIN 2017/06/29 Don't show availability messages
			/*
            $o .= $this->section_availability_message($section,
                has_capability('moodle/course:viewhiddensections', $context));
                */
        } else {
            // When on a section page, we only display the general section title, if title is not the default one.
            $hasnamesecpg = ($section->section == 0 && (string) $section->name !== '');

            if ($hasnamesecpg) {
                $o .= $this->output->heading($this->section_title($section, $course), 3, 'section-title');
            }
            $o .= \html_writer::start_tag('div', array('class' => 'summary'));
            $o .= $this->format_summary_text($section);

            if ($this->userisediting && has_capability('moodle/course:update', $context)) {
                $url = new \moodle_url('/course/editsection.php', array('id' => $section->id, 'sr' => $sectionreturn));
                $o .= \html_writer::link($url,
                    \html_writer::empty_tag('img',
                        array('src' => $this->output->pix_url('t/edit'),
                        'class' => 'iconsmall edit', 'alt' => get_string('edit'))),
                        array('title' => get_string('editsection', 'format_topcoll'))
                );
            }
            $o .= \html_writer::end_tag('div');

            $o .= $this->section_availability_message($section,
                has_capability('moodle/course:viewhiddensections', $context));
        }
        return $o;
    }

    
    
    }