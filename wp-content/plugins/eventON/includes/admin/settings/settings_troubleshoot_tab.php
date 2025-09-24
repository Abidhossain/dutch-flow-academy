<?php
/**
 * EventON Settings tab - Troubleshoot/support
 * @version 4.7
 * 
 */
?>
<div id="evcal_5" class="postbox evcal_admin_meta curve">	

	<div class='evopad20'>
	<?php 

	ob_start();

	?>
	<div class="evotrouble_qas">
	<?php

		$qas = apply_filters('eventon_troubleshooter',array(
			'Frequently Asked Questions'=> array(
				'Why doesn’t my shortcode work?'=> 'One reason why your short code might not work is if there are commas in the short code. Your short code should look like this without any commas separating the variables. Also when you enter the shortcode make sure to switch back to “Text” mode in wordpress text editor to check that the shortcode is cleanly typed without any HTML tags inside.',
				'How can I change the fonts on the Calendar?'=> 'In the WordPress backend under Event Calendar Setting >Appearance you can find the below input field “Primary Calendar Font Family” where you can write the name of font that you would like to use.	NOTE: make sure this font is either supported via webfonts on @font-face in your website. Also if the font name is something like “Times New Roman” make sure to type that inside quotation marks.',
				'How do I change the time to 24 hour format instead or AM/PM?'=>'Go to Settings> General on the lower part on this page you should find “Date Format” and “Time Format” settings for your website. Simply making changes in here to reflect 24 hour time format will change the time on Event Calendar to 24 hour time format.',
				'Some data fields (like RSVP box, Event Tickets etc.) are not showing in the eventcard, why is this?'=>'Go to <b>myeventon> settings> EventCard Design</b> and make sure to add these fields into the designer.',
				'How do I show more fields on event top?'=>'Go to  <b>myEventon> Settings> EventTop</b>  and in the EventTop Designer add desired fields to show on frontend.',
				'Add to calendar time is incorrect'=>'Go to  <b>Settings > General> Timezone</b> and make sure the timezone set is correct timezone for your location. Add to calendar ICS and google calendar will adjust time based on this timezone value set.',
				'How to get the tile view'=>'Inside the eventon shortcode add tiles variable so it would look like this <code>[add_eventon tiles="yes"]</code>',
				'How can I optimize the JS files'=>'EventON does not offer JS file optimization, however you can use https://wordpress.org/plugins/autoptimize/ WP plugin to easily optimize.',

			),
			'Common Issues'=>array(
				'Why is google maps showing blank box?'=>'One common solution is: <br/>Go to <b>myEventon> Settings> Google Maps API</b> and click disable google maps API and select google maps javascript file only.
					<br/><br/> Another solution is to inspect your website on front end to see what issues you are seeing. <a href="http://www.myeventon.com/documentation/why-are-my-events-are-not-sliding-down-or-months-not-switching/" target="_blank">Follow these guidelines to perform front-end inspection</a>',
				'Calendar does not switch months or load same events'=>'This happen when there is a javascript error on your website. Solution is to inspect your website on front-end to see what issues you are seeing. <a href="http://www.myeventon.com/documentation/why-are-my-events-are-not-sliding-down-or-months-not-switching/" target="_blank">Follow these guidelines to perform front-end inspection</a>',
				'All my events are not showing in the calendar'=>'EventON should show all your events in the calendar. <a href="http://www.myeventon.com/documentation/all-the-events-are-not-showing-in-calendar/" target="_blank">See the common reasons why this happens and solutions to it</a>',
				'How to find if the issue is indeed coming from EventON?'=>'When you use multiple plugins and themes, it is possible eventON does not play nice with those due to something different they do than standard procedure. <a href="http://www.myeventon.com/documentation/how-to-find-if-an-issue-is-coming-from-eventon/" target="_blank">Follow these guidelines to see if it is EventON that is causing the error.</a>'
			)
		));

		foreach($qas as $section=>$questions){
			echo '<h4>'.$section.'</h4>';
			foreach($questions as $question=>$answer){
				echo '<h5>'.$question.'</h5><p style="display:none">'.$answer.'</p>';
			}
		}

	?>
	</div>
	<?php 

	$faq_html = ob_get_clean();

	// licenses code content
	ob_start();
	echo "<p class='evomart10i evopadt10i'>". __('Licenses code to verify valid EventON licenses in order to receive EventON support') ."</p>";
	EVO()->elements->print_trigger_element([
		'id'=> 'evoaddons_lics',
		'title'=> __('Licenses Code','eventon'),
		'adata'=>[
			'a'=> 'eventon_lics_code',
			'data'=> ['type'=> 'all']
		],
		'lbdata'=> [
			'class'=> 'evoaddons_lics',
			'title'=> __('EventON License Codes','eventon'),
		]
	],'trig_lb');
	$lics_code = ob_get_clean();
	$lics_code = '';
	

	EVO()->elements->get_grid_content( array(
			'box_1'=> array(
				'sizes'=> '6,12,12',
				'header'=> "<h3 class='evotac evoff_1 evofz36'> ". __('EventON Documentation','eventon') ."</h3>",
				'body'=> "<div class='eventon_searchbox evopad15'> 
					<form role='search' target='_blank' action='https://docs.myeventon.com/' method='get' id='searchform' class='evoposr'>
						<input class='evobr20' type='text' name='s' placeholder='Search Documentation'/>
						<input type='hidden' name='post_type' value='document' /> <!-- hidden 'products' value -->
						<input type='submit' alt='Search' value='Search' />
					</form>
				</div>",
				'footer'=>"<div class='evopad15 evotac evomarb20'><p style=''><i>" . __('NOTE: Please feel free to type in your question and search our documentation library for related answers','eventon') . "</i></p>
					<a href='https://www.youtube.com/playlist?list=PLj0uAR9EylGrROSEOpT6WuL_ZkRgEIhLq' class='evomart10 evo_admin_btn btn_prime' target='_blank'><i class='fa fa-play evomarr10'></i> " . __('EventON Video Tutorials','eventon') . "</a></div>",
				'styles'=>'background-color:var(--evo_color_second);',
			),
			'box2' => array(
			    'sizes' => '3,6,12',
			    'header' => "<h3 class='evoff_1 evotac evolh12'><i class='evodb fa fa-circle-info evomarb20 evofz30'></i>". __('Having Issues with EventON?', 'eventon') ."</h3>",
			    'body' => "<div class='evopad20 evotac'>
			               <p>Read our <a href='http://docs.myeventon.com/documentations/check-eventon-working/' target='_blank'>Troubleshooting Guide</a> and identify your issue and apply common solutions to solve the issues before contacting us.</p>
			               <div class=''>{$lics_code}</div>
			        </div>",
			    'styles'=>'background-color:#cbe0ff;',
			),
			'box5' => array(
			    'sizes' => '3,6,12',
			    'header' => "<h3 class='evoff_1 evotac evolh12' style='color:#fff;'>". __('Follow us on', 'eventon') ."<i class='fab fa-x-twitter evomarl5'></i></h3>",
			    'body'=> "<div class='evotac evopadl20 evopadr20'>
			    	<a style='display:inline-block' class='evo_admin_btn btn_triad evomart10' href='http://www.x.com/myeventon' target='_blank'>@myeventon</a>
			    	<p>".__('You can get the latest updates, other news, tips and tricks for eventON via our twitter stream.','eventon') ."</p>
			    	</div>",
			   	'styles'=>'background-color:var(--evo_color_prime);color:#fff;',
			),

			'box3' => array(
			    'sizes' => '12,12,12',
			    'header' => "<h3 class='evoff_1 evofz24'>". __('Common Issues and Solutions', 'eventon') ."</h3>",
			    'styles'=>'background-color:#e6e6e6;',
			    'body'=> "<div class='evopadl30 evopadr30 evopadb20'>
			    	<p>Are you experiencing issues with EventON? Please look through our common questions/issues below and the solutions to them before contacting us. You <b>do NOT</b> need to purchase support from codecanyon. If you purchase our software and have a valid license, we will support you :)</p>
			    </div>
			    <div class=''>
			    {$faq_html}
			    </div>",
			),
			'box4' => array(
			    'sizes' => '12,12,12',
			    'header' => "<h3 class='evoff_1 evotac'>". __('Official Support Helpdesk', 'eventon') ."</h3>",
			    'body'=> "<div class='evopadl30 evopadr30 evopadb20 evotac'>
			    	<a style='margin-top:8px; display:inline-block' class='evo_admin_btn btn_prime' href='http://helpdesk.ashanjay.com' target='_blank'>EventON HelpDesk</a>
			    	<p>". __('This is our official support system. Please feel free to search for already asked questions and ask your support questions for our help. EventON <b>Purchase code</b> is required to access helpdesk.','eventon') ."</p>
			    	</div>",
			   	'styles'=>'background-color:var(--evo_color_second);',
			),
			
		)
	);

	?>	
	</div>
</div>