<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class WprHtml {

	protected function echo_title( $title, $class = '' )
	{
		echo '
		<h2 class="' . $class . '">' . $title . '</h2>';
	}
	protected function echo_updated( $text )
	{
		$this->echo_message( $text, 'updated' );
	}
	protected function echo_error( $text )
	{
		$this->echo_message( $text, 'error' );
	}
	protected function echo_message( $text, $class )
	{
		echo '
			<div class="' . $class . '">
				<p>' . $text . '</p>
			</div>';
	}

	protected function echo_form_opener()
	{
		echo '
			<form method="POST" action="options.php">';

		settings_fields( 'wpr-settings' );
		do_settings_sections( 'wpr-settings' );
	}
	protected function echo_form_table_opener( )
	{
		echo '
						<table class="form-table">';
	}
	protected function echo_form_table_row( $th, $td )
	{
		echo '
							<tr>
								<th>' . $th . '</th>
								<td>' . $td . '</td>
							</tr>';
	}
	protected function echo_form_table_closer()
	{
		echo '
						</table>';
	}
	protected function echo_form_closer()
	{
		echo '
			</form>';
	}
	
	protected function html_checkbox( $optn, $valu = 'yes', $type = 'checkbox' )
	{
		$v = $this->html_input_get_vars( $optn );
		$chck = ( $valu == $v[ 'value' ] );
		return '
			<input type="' . $type . '" name="' . $v[ 'name' ] . '" id="' . $v[ 'id' ] . '" value="' . $valu . '"' . ( $chck ? ' checked' : '' ) . ' />';
	}
	protected function html_input( $optn, $type = 'text', $extr = '' )
	{
		$v = $this->html_input_get_vars( $optn );
		return '
			<input type="' . $type . '" name="' . $v[ 'name' ] . '" id="' . $v[ 'id' ] . '" value="' . esc_attr( $v[ 'value' ] ) . '"' . ( $extr ? ' ' . $extr : '' ) . ' />';
	}
	protected function html_textarea( $optn )
	{
		$v = $this->html_input_get_vars( $optn );
		return '
			<textarea name="' . $v[ 'name' ] . '" id="' . $v[ 'id' ] . '">' . esc_attr( $v[ 'value' ] ) . '</textarea>';
	}
	protected function html_select( $optn, $opts )
	{
		$v = $this->html_input_get_vars( $optn );
		$html = '
			<select name="' . $v[ 'name' ] . '" id="' . $v[ 'id' ] . '">';
		
		foreach( $opts as $valu => $text )
		{
			$html .= '
				<option value="' . $valu . '"' . ( ( $valu == $v[ 'value' ] ) ? ' selected' : '' ) . '>' . $text . '</option>';
		}
		$html .= '
			</select>';

		return $html;
	}

	private function html_input_get_vars( $optn )
	{
		return array(
			'id'	=> $optn[ 1 ] . '_' . $optn[ 2 ],
			'name' 	=> $optn[ 1 ] . '[' . $optn[ 2 ] . ']',
			'value'	=> !empty( $optn[ 0 ][ $optn[ 2 ] ] ) ? $optn[ 0 ][ $optn[ 2 ] ] : ''
		);
	}
}
