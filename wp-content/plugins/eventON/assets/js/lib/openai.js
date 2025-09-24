/**
 * Open AI Admin Scripts
 * @version 4.9.7
 */

jQuery(document).ready(function($) {

// show and hide the AI assist
	$('body').on('click','.evoai_trig_open',function(){		
		call_ai_creator( 0, '');
	}).on('click','.evoai_trig_close',function(){
		hide_ai_bar();		
	});

// Add Wand to places
	$('body').on('evo_eventedit_all_dom_loaded', function(){
		$('#evcal_subtitle')
			.after('<i class="evoai_trig fa fa-wand-magic-sparkles evoposa evot0 evor10 evoh100p evodfx evofxaic evocurp evohoop7" data-val="subtitle" data-l="1"></i>');
	});
	
// any AI trigger item
$('body').on('click', '.evoai_trig', function() {
	let val = $(this).data('val');
    let level = $(this).data('l'); // Get the level from data-l
    
    call_ai_creator( level, val);    
});

// call to create AI Creator
function call_ai_creator( level , val ){
	if( !$('#evoai_bar').hasClass('show') ) $('.evoai_bar_in_main').addClass('appearing');
	$('#evoai_bar').not('.show').addClass('show').find('.evoai_bar_in').removeClass('loading');	
	load_ai_creator( level, val);    
}

// load AI creator
var evoai_vals1 = ['Title', 'Subtitle', 'Description', 'Create X Post'];
var evoai_vals2 = [ 'Generate', 'Rewrite', 'Engaging', 'Creative', 'Casual', 'Enthusiastic', 'Professional', 'Concise', 'Call-to-action'];
var evoai_data = {l1:'',l2:''};

function load_ai_creator( level , val ){
	// Update evoai_data
    evoai_data = {
        l1: level === 1 ? val : (level === 0 ? '' : evoai_data.l1),
        l2: level === 2 ? val : (level <= 1 ? '' : evoai_data.l2)
    };  

    let newContent = '';

	if (level === 0) {
		// Get event title and subtitle values
        let event_title = $('#title').val();
        if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
            event_title = wp.data.select('core/editor').getEditedPostAttribute('title');
        }
        if (!event_title || event_title.trim() === '') {            
            availableOptions = evoai_vals1.filter(option => option !== 'Create X Post'); // Exclude Generate when has content
        }

        // Generate buttons dynamically from evoai_vals1 for level 0
        let buttonsHTML = evoai_vals1.map(value => 
            `<button class='evoai_trig evo_admin_btn' data-l='1' data-val='${value.toLowerCase().replace(/\s+/g, '-')}'>${value}</button>`
        ).join('');


        
        // Create the new HTML content for level 0
        $('body').find('.evoai_assist_now').html('');
        newContent = `<div class='evodfx evofxdrr evogap10 evofx_ww'>${buttonsHTML}</div>`;
    
    } else if (level === 1) {
    	// Find the original text from evoai_vals1 using evoai_data.l1
        let selectedText = evoai_vals1.find(value => 
            value.toLowerCase().replace(/\s+/g, '-') === evoai_data.l1
        ) || 'Unknown'; // Fallback if not found

        let content = '';
        let event_title = '';
        let subtitle = '';

        // Get event title and subtitle values
        event_title = $('#title').val();
        if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
            event_title = wp.data.select('core/editor').getEditedPostAttribute('title');
        }
        subtitle = $('#evcal_subtitle').val();
        // event description
        if ( evoai_data.l1 === 'description' || evoai_data.l1 === 'create-x-post' ) {
            // Get the post content
            if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
                // Block Editor (Gutenberg)
                content = wp.data.select('core/editor').getEditedPostContent();
            } else {
                // Classic Editor
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                    // If TinyMCE is active
                    content = tinyMCE.get('content').getContent();
                } else {
                    // Plain text editor
                    content = $('#content').val() || '';
                }
            }
        }

        // Determine which buttons to show based on l1 value and content
        let availableOptions = evoai_vals2; // Full list: ['Generate', 'Rewrite', 'Engaging', etc.]

	    switch (evoai_data.l1) {
		    case 'title':
		        availableOptions = (!event_title || event_title.trim() === '') 
		            ? ['Generate'] 
		            : evoai_vals2.filter(option => option !== 'Generate');
		        break;
		    case 'description':
		        availableOptions = (!content || content.trim() === '') 
		            ? ['Generate'] 
		            : evoai_vals2.filter(option => option !== 'Generate');
		        break;
		    case 'subtitle':
		        availableOptions = (!subtitle || subtitle.trim() === '') 
		            ? ['Generate'] 
		            : evoai_vals2.filter(option => option !== 'Generate');
		        break;
		    case 'create-x-post':
		        availableOptions = evoai_vals2.filter(option => !['Generate', 'Rewrite'].includes(option));
		        break;
		}

        // Generate buttons dynamically from filtered options
        let buttonsHTML = availableOptions.map(value => 
            `<button class='evoai_trig evo_admin_btn' data-l='2' data-val='${value.toLowerCase().replace(/\s+/g, '-')}'>${value}</button>`
        ).join('');

        
        // Create the new HTML content for level 1
        $('body').find('.evoai_assist_now').html( `<span class='evoai_trig evocurp evobrdB1 evobr20 evolh1 evoHcw evoHbgcprime' data-l='0' style="    padding: 3px 10px;"><i class='fa fa-chevron-left evodn evomarr5 evofz12'></i>${selectedText}</span>` );
        newContent = `<div class='evodfx evofxdrr evogap5 evofxww evofxjcc'>${buttonsHTML}</div>`;
    } else if (level === 2) {
        // Get the post title based on the editor type
        let content = '';
        let event_title = '';
        let subtitle = '';

        // get event title
            event_title = $('#title').val();
            if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
                event_title = wp.data.select('core/editor').getEditedPostAttribute('title');
            }

        // subtitle 
            subtitle = $('#evcal_subtitle').val();

        // event description
        if ( evoai_data.l1 === 'description' || evoai_data.l1 === 'create-x-post' ) {
            // Get the post content
            if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
                // Block Editor (Gutenberg)
                content = wp.data.select('core/editor').getEditedPostContent();
            } else {
                // Classic Editor
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                    // If TinyMCE is active
                    content = tinyMCE.get('content').getContent();
                } else {
                    // Plain text editor
                    content = $('#content').val() || '';
                }
            }
        }
       	
       	$(this).evo_admin_get_ajax({
            adata: {
                a: 'evoai_enhance_content',
                data: {
                    l1: evoai_data.l1,// data type
                    l2: evoai_data.l2,// mode
                    title: event_title,
                    subtitle: subtitle,
                    content: content
                }
            },uid:'evoai_enhance_content_trig'
        });        
    }

    if (newContent) $('body').find('.evoai_content').html(newContent); 

    setTimeout(function(){ $('.evoai_bar_in_main').removeClass('appearing');}, 800);
}

function hide_ai_bar(){
	$('#evoai_bar').removeClass('show');
	$('.evoai_bar_responses').html('');
	load_ai_creator( 0,'');
}

$('body')
.on('evo_ajax_beforesend_evoai_enhance_content_trig',function(event, OO,  el){
	$('body').find('.evoai_bar_in_main').addClass('loading');
	$('body').find('.evoai_icon').removeClass('fa-wand-magic-sparkles').addClass('fa-spinner');
})
// API results display
	.on('evo_ajax_success_evoai_enhance_content_trig',function(event, OO, data, el){
		//console.log( data);
		$('body').find('.evoai_bar_in_main').removeClass('loading');
		$('body').find('.evoai_icon').addClass('fa-wand-magic-sparkles').removeClass('fa-spinner');
		
		if (data.success && data.contents && Array.isArray(data.contents)) {
	        const $responsesContainer = $('body').find('.evoai_bar_responses');
	        const $dataType = OO.ajaxdata.l1;

	        // Loop through each content item and append with delay
	        data.contents.forEach((item, index) => {
	            const $applyResponse = $dataType !== 'create-x-post' 
	                ? `<i class='evoai_trig_apply fa fa-chevron-right evofz18 evocurp evohoop7 evotooltipfree' title="${evoai_para.apply_response}"></i>` 
	                : '';
	            let cleanedItem = item.replace(/^"|"$/g, '');
	            const htmlContent = `
	                <div class='evoai_bar_in_response evoai_bar_in evobgcw evobr20 evopad15 evow100p evoboxbb evodfx evofxdrr evofxjcsb evofxaifs evomarb10 animate-in' style='animation-delay: ${index * 0.2}s;'>
	                    <span class='evoai_response evomarr10'>${cleanedItem}</span>
	                    <div class='evodfx evogap10'>
	                        <i class='evoai_trig_copy fa fa-copy evofz18 evocurp evohoop7 evotooltipfree' title="${evoai_para.copy_response}"></i>${$applyResponse}
	                    </div>
	                </div>
	            `;

	            // Append each item individually
	            $responsesContainer.append(htmlContent);
	        });

	        // Scroll to the bottom after all items are added
	        setTimeout(() => {
	            $responsesContainer.scrollTop($responsesContainer[0].scrollHeight);
	        }, data.contents.length * 200); // Delay scroll until animations finish
	    } else {
			$(el).evo_snackbar({	message: data.msg || 'Something went wrong',	});
		}
	})
// copy response
	.on('click','.evoai_trig_copy',function(){
		let response_box = $(this).closest('.evoai_bar_in');
		let response = response_box.find('.evoai_response').text();

		// Copy response to clipboard
	    navigator.clipboard.writeText(response).then(function() {
	        // Optional: Show success feedback (e.g., via snackbar)
	        $(this).evo_snackbar({   message: 'Copied to clipboard!',  visible_duration: 2000  });
	    }).catch(function(err) {
	        // Optional: Handle errors
	        console.error('Failed to copy to clipboard:', err);
	        $(this).evo_snackbar({ message: 'Failed to copy. Please try again.', visible_duration: 2000  });
	    });
	})
// apply response
.on('click','.evoai_trig_apply',function(){
	let response_box = $(this).closest('.evoai_bar_in');
	let response = response_box.find('.evoai_response').text();

	// update title
	if (evoai_data.l1 === 'title') {
		// Check if Block Editor (Gutenberg) is active
        if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
            // Update title in Block Editor
            wp.data.dispatch('core/editor').editPost({ title: response });
        } else {
            // Update title in Classic Editor
            $('#title').val(response);
        }        
	}

	// update subtitle
	if (evoai_data.l1 === 'subtitle') {
		$('#evcal_subtitle').val(response);
	}

	if (evoai_data.l1 === 'description') {
        // Update content
        if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
            // Block Editor (Gutenberg)
            const blocks = wp.blocks.parse(response) || [wp.blocks.createBlock('core/paragraph', { content: response })];
            wp.data.dispatch('core/editor').resetBlocks(blocks);
        } else {
            // Classic Editor
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                // TinyMCE active
                tinyMCE.get('content').setContent(response);
            } else {
                // Plain text editor
                $('#content').val(response);
            }
        }
    }

    // hide any tooltipts
	 $('body').trigger('evoelm_hideall_tooltips');

    hide_ai_bar();
})


;

});