<?php

class pluginPublicAnnouncements extends Plugin {

	public function init()
	{
		// JSON database
		$jsondb = json_encode(array(
			'0000'=>array(
                'text'=>"Example Announcement",
			    'active'=>true,
                'type'=>'info',
                'color'=>'light-blue'
		      ),
            '0001'=>array(
                'text'=>"Example Announcement",
			    'active'=>true,
                'type'=>'info',
                'color'=>'light-blue'
		      )
        ));

		// Fields and default values for the database of this plugin
		$this->dbFields = array(
			'selector'=>'.body',
            'position'=>'before',
			'jsondb'=>$jsondb
		);

		// Disable default Save and Cancel button
		$this->formButtons = false;
	}

	// Method called when a POST request is sent
	public function post()
	{
		// Get current jsondb value from database
		// All data stored in the database is html encoded
		$jsondb = $this->db['jsondb'];
		$jsondb = Sanitize::htmlDecode($jsondb);

		// Convert JSON to Array
		$pas = json_decode($jsondb, true);

		// Check if the user click on the button delete or add
		if( isset($_POST['deletePA']) ) {
			// Values from $_POST
			$code = $_POST['deletePA'];

			// Delete the link from the array
			unset($pas[$code]);
		}
		elseif( isset($_POST['addPA']) ) {
			
            
            $text = $_POST[$_POST['addPA'].'_text'];
            
            if(empty($_POST['addPA'])) $code = substr(sha1($text), 0, 4);
            else $code = $_POST['addPA'];
            
           
			// Check empty string
			if( empty($text) ) { return false; }

			// Add the PA
			$pas[$code]['text'] = $text;
		}

		// Encode html to store the values on the database
		$this->db['selector'] = Sanitize::html($_POST['selector']);
		$this->db['position'] = Sanitize::html($_POST['position']);
		$this->db['jsondb'] = Sanitize::html(json_encode($pas));
		
        
        $this->db['post'] = Sanitize::html(json_encode($_POST));
        
        

		// Save the database
		return $this->save();
	}

	// Method called on plugin settings on the admin area
	public function form()
	{
		global $L;
        
		$html  = '<div class="alert alert-primary" role="alert">';
		$html .= $this->description();
		$html .= '</div>';

        //settings
		$html .= '<div>';
		$html .= '<label>'.$L->get('Selector').'</label>';
		$html .= '<input name="selector" class="form-control" type="text" value="'.$this->getValue('selector').'">';
		$html .= '<span class="tip">'.$L->get('define the selector to hook announcements before or after').'</span>';
		$html .= '</div>';
        
        $html .= '<div>';
		$html .= '<label>'.$L->get('position').'</label>';
        $html .= '<select name="position">';
        $html .= '<option value="before">before</option>';
        $html .= '<option value="after">after</option>';
        $html .= '</select>';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<button name="save" class="btn btn-primary my-2" type="submit">'.$L->get('Save').'</button>';
		$html .= '</div>';

		// New link, when the user click on save button this call the method post()
		// and the new link is added to the database
		$html .= '<h4 class="mt-3">'.$L->get('Add a new link').'</h4>';

		$html .= '<div>';
		$html .= '<label>'.$L->get('Text').'</label>';
		$html .= '<input name="text" type="text" class="form-control">';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<button name="addPA" class="btn btn-primary my-2" type="submit">'.$L->get('Add').'</button>';
		$html .= '</div>';

		// Get the JSON DB, getValue() with the option unsanitized HTML code
		$jsondb = $this->getValue('jsondb', $unsanitized=false);
		$links = json_decode($jsondb, true);

		$html .= !empty($links) ? '<h4 class="mt-3">'.$L->get('Links').'</h4>' : '';

		foreach($links as $code=>$array) {
			$html .= '<div class="my-2">';
			$html .= '<label>'.$L->get('Name').'</label>';
			$html .= '<input type="'.$code.'_text" class="form-control" value="'.$array['text'].'">';
			$html .= '</div>';

            $html .= '<div>';
		    $html .= '<button name="addPA"  value="'.$code.'" class="btn btn-primary my-2" type="submit">'.$L->get('Save').'</button>';
		    $html .= '</div>';

			$html .= '<div>';
			$html .= '<button name="deletePA" class="btn btn-secondary my-2" type="submit" value="'.$code.'">'.$L->get('Delete').'</button>';
			$html .= '</div>';
            
           

		}

		return $html;
	}

	// Method called on the sidebar of the website
	public function siteHead()
	{
		global $L;
        
        $selector = $this->getValue('selector');
        $position = $this->getValue('position');
        

		// HTML
		$html = '<div id="pas">';

		// Get the JSON DB, getValue() with the option unsanitized HTML code
		$jsondb = $this->getValue('jsondb', false);
		$links = json_decode($jsondb);

		// By default the database of categories are alphanumeric sorted
		foreach( $links as $text=>$settings ) 
        {
            $class = "warning";
            
            $html .= '<div class="z-depth-1 pa '.$class.'">';
            $html .= $text;
            $html .= '</div>';
		}


 		$html .= '</div>';
        
        
        $script = Theme::jquery();
$script .= <<<EOS

<script>
    
    var \$pa = jQuery.noConflict();
  \$pa( document ).ready(function() {
    \$pa('{$selector}').{$position}('{$html}');
  });
</script>
<style>

    
 #pas{
    margin: 0 16px;
    margin-top: 8px;
   
   }
   
   .pa{
    padding:8px;
   }
   
   #pas > .pa.info{
    background-color: #FFC107;
   }
   
    #pas > .pa.warning{
    background-color: #FFC107;
   }
   
    #pas > .pa.red{
    background-color: #FFC107;
   }
   
   </style>

EOS;
     

		return $script;
	}
}
