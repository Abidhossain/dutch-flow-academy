<?php
/**
 * EventON Product Information for Sections in new update information
 * @version 4.7.2
 */

class EVO_Plugins_API_Data{

	function get_data($name, $slug){
		$eventon_product_information = apply_filters('evo_pluginsapi_sections',
		array(
			'eventon'=>array(
				'description'	=>$this->get_general('description','EventOn', 'eventon'),
				'installation'	=>$this->get_general('installation','EventOn', 'eventon'),
				'register_license'=>'<p><strong>Get free updates</strong></p><p>In order to get free EventON updates and download them directly in here <strong>activate</strong> your copy of EventON with proper license.</p><p><strong>How to get your license key</strong></p><ol><li>Login into your Envato account</li><li>Go to Download tab</li><li>Under EventON click "License Cerificate"</li><li>Open text file and copy the <strong>Item Purchase Code</strong></li><li>Go to myEventON in your website admin</li><li>Under "Licenses" tab find the EventON license and click "Activate Now"</li><li>Paste the copied purchased code from envato, and click "Activate Now"</li><li>Once the license if verified and activated you will be able to download updates automatically</li></ol><br/><br/><p><a href="http://www.myeventon.com/documentation/how-to-find-eventon-license-key/" target="_blank">Updated Documentation</a></p>', 
				'changelog'		=> $this->get_general('changelog','EventOn', 'eventon'),
			),

		));
	
		$output = isset($eventon_product_information[$slug])? $eventon_product_information[$slug]: array();

		// Defaults for all addons
		$output['eventon_reviews'] = $this->evo_reviews();
		$output['latest_news'] = $this->get_general('latest_news','EventOn', 'eventon');
		if(!isset($output['description'])) $output['description'] = $this->get_general('description','EventOn', 'eventon');
		
		return $output;
	}

	function get_general($section, $name, $slug){
		switch ($section) {
			case 'latest_news':

				ob_start();
				?>
				<p>Make sure to follow us via X <a href="https://x.com/myeventon" target="_blank" rel="noopener noreferrer">@myeventon</a> for updates.</p>

				<p>Check out our latest news updates at <a href="https://www.myeventon.com/news/" target="_blank" rel="noopener noreferrer">EventON News</a></p>
				<?php 
				return ob_get_clean();

			break;
			case 'description':
				return "<p>EventOn <b>#1 Best Selling</b> WordPress Event Calendar in codecanyon!</p><p>EventOn provide a stylish and minimal calendar design that address to the needs of your visitors and audience. It is also packed with awesome features such as: Repeat events, multi-day events, google map locations, smooth month navigation, featured images, and the list goes on.</p><p>To learn more about eventON please visit <a href='http://www.myeventon.com'>myeventon.com</a>";
			break;
			case 'installation':
				ob_start();
			    ?>
			    <h4>Minimum Requirements:</h4>
			    <p>WordPress 6.0 or higher, PHP 8.0 or higher, MySQL 5.0 or higher</p>

			    <h4>Automatic Installation</h4>
			    <p>In order to get automatic updates you will need to activate your version of <?php echo $name;?>. You can learn how to activate this plugin <a href='http://www.myeventon.com/documentation/how-to-get-new-auto-updates-for-eventon/' target='_blank'>in here</a>. Automatic updates will allow you to perform one-click updates to EventOn products direct from your wordpress dashboard.</p>

			    <h4>Manual Installation</h4>
			    <p><strong>Step 1:</strong></p>
			    <p>Download <code><?php echo $slug;?>.zip</code> from <?php echo ($slug=='eventon')? 'codecanyon > my downloads':'<a href="http://myeventon.com/my-account" target="_blank">myeventon.com/my-account</a>';?></p>
			    <p><strong>Step 2:</strong></p>
			    <p>Unzip the zip file content into your computer. </p>
			    <p><strong>Step 3:</strong></p>
			    <p>Open your FTP client and remove files inside <code>wp-content/plugins/<?php echo $slug;?>/</code> folder. </p>
			    <p><strong>Step 4:</strong></p>
			    <p>Update the zip file content into the above mentioned folder in your FTP client. </p>
			    <p><strong>Step 5:</strong></p>
			    <p>Go to <code>../wp-admin</code> of your website and confirm the new version has indeed been updated.</p>

			    <p><a href="http://www.myeventon.com/documentation/can-download-addon-updates/" target="_blank">More information on how to download & update eventON plugins and addons</a></p>
			    <?php
			    return ob_get_clean();
			break;
			case 'changelog':
				ob_start();
				?>
				<h4><?php echo $name;?> Changelog</h4>
				<p>Complete updated changelog for <?php echo $name;?> can be found at <a target="_blank" href="http://www.myeventon.com/documentation/"><?php _e('EventON Changelog','eventon');?>.</a></p>

				<h4>Support with EventON</h4>
				<p>For support & frequently asked questions, visit <a target="_blank" href="https://www.myeventon.com/support/"><?php _e('The EventON Support Forums','eventon');?></a></p>
				<?php 

				return ob_get_clean();
			break;
		}
	}

	// @updated 4.7.2
	function evo_reviews(){
		ob_start();
		foreach(array(
			1=>array(
				'title'=> 'Customizability',
				'userurl'=> 'https://codecanyon.net/user/cnetworking',
				'img'=> '',
				'username'=> 'cnetworking',
				'date'=> '1 month ago',
				'review'=>	"Lot's of options. It's unfortunately the Full Calendar doesn't come with it but I get the need for add-ons. Customer service has always been great to work with (if a little bit slow at times)...they have always helped to resolve problems I've faced. We use EventOn on multiple sites and will be using it more in the future!"
			),
			2=>array(
				'title'=> 'Customer Support',
				'userurl'=> 'https://codecanyon.net/user/JScottMO',
				'img'=> '',
				'username'=> 'JScottMO',
				'date'=> '3 months ago',
				'review'=>	"I have been using for years, without flaw. After a recent change of hosting providers, I had some odd behavior of my event slider. I was nervous about using tech support, since I had never contacted them, but they were excellent. Not only did they fix my issue, but they also noticed that with a couple of small additions to my css, they could make my slider look even better with the images I was using! Very happy customer. Scott"
			),
			3=>array(
				'title'=> 'Design Quality',
				'userurl'=> 'https://codecanyon.net/user/Lorena001',
				'img'=> '',				
				'username'=> 'Lorena001',
				'date'=> '3 months ago',
				'review'=>	"Sometimes even though you pay for a plugin it doesn't mean you receive quality.This does not happen with the EventOn Plugin.It has many options that allow you to customize even the smallest detail and a support department that is always willing to help.In short, a perfect complement for any website that achieves a very professional image."
			),4=>array(
				'title'=> 'Design Quality',
				'userurl'=> 'https://codecanyon.net/user/vhlouzado',
				'img'=> '',				
				'username'=> 'vhlouzado',
				'date'=> '5 months ago',
				'review'=>	'I have been using the plugin for a few years. In addition to having a clean and elegant design, it is fast, has constant updates and good technical support.'
			)
		) as $info){
			?>
			<div class='review'>
				<div class="review-head">
					<div class="reviewer-info">
						<div class="review-title-section">
							<h4><?php echo $info['title'];?></h4>
							<div class="star-rating"><div class="wporg-ratings"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div></div>
						</div>
						<p>
							By <a href="<?php echo $info['userurl'];?>" target="_blank"><img style='height:20px' alt="" src="<?php echo $info['img'];?>" class="avatar avatar-16 photo"></a><a href="<?php echo $info['userurl'];?>" target="_blank"><?php echo $info['username'];?></a> on <span class="review-date"><?php echo $info['date'];?></span>			
						</p>
					</div>
				</div>
				<div class="review-body"><?php echo $info['review'];?></div>
			</div>

			<?php
		}
		return ob_get_clean();
	}

}