<?php

class cfs_single extends cfs_field
{

	function __construct() {
		$this->name = 'single';
		$this->label = __( 'Single Post Object', 'cfs' );
	}


	function html( $field ) {

		$available_posts = $all_post_types = array();

		$post_types = array();
		if ( ! empty( $field->options['post_types'] ) ) {
			foreach ( $field->options['post_types'] as $type ) {
				$post_types[] = $type;
			}
		}
		else {
			$post_types = get_post_types( array( 'exclude_from_search' => true ) );
		}

		// Deprecated - use "cfs_field_relationship_query_args"
		$post_types = apply_filters( 'cfs_field_relationship_post_types', $post_types );

		$args = array(
			'post_type' => $post_types,
			'post_status' => array( 'publish', 'private' ),
			'posts_per_page' => -1,
			'fields' => 'ids',
			'orderby' => 'title',
			'order' => 'ASC'
		);

		$args = apply_filters( 'cfs_field_relationship_query_args', $args, array( 'field' => $field ) );
		$query = new WP_Query( $args );

		foreach ( $query->posts as $post_id ) {
			$post = get_post( $post_id );
			$post_title = ( 'private' == $post->post_status ) ? '(Private) ' . $post->post_title : $post->post_title;
			$available_posts[$post->post_type][] = array(
				'ID' => $post->ID,
				'post_type' => $post->post_type,
				'post_status' => $post->post_status,
				'post_title' => $post_title,
			);
			if( ! in_array( $post->post_type, $all_post_types ) ) {
				$all_post_types[] = $post->post_type;
			}
		}

		$use_optgroup = count( $all_post_types ) > 1 ? true : false;
		?>
		<select name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>">
			<option value="0">- Select -</option>
			<?php $last_post_type = ''; ?>
			<?php foreach ($available_posts as $post_type => $posts ) : ?>
				<?php foreach( $posts as $post ) : ?>
					<?php if ( $use_optgroup && $post_type !== $last_post_type ) : ?>
						<?php $post_type_obj = get_post_type_object( $post_type ); ?>
						</optgroup>
						<optgroup label="<?php echo esc_attr( $post_type_obj->label ); ?>">
					<?php $last_post_type = $post_type; endif; ?>
					<?php $selected = in_array( $post['ID'], (array) $field->value ) ? ' selected' : ''; ?>
					<option value="<?php echo absint( $post['ID'] ); ?>"<?php echo $selected; ?>><?php echo esc_html( $post['post_title'] ); ?></option>
				<?php endforeach; ?>
			<?php endforeach; ?>
			<?php if( $use_optgroup ) : ?>
				</optgroup>
			<?php endif; ?>
		</select>
	<?php
	}


	function options_html( $key, $field ) {

		$post_types = isset( $field->options['post_types'] ) ? $field->options['post_types'] : null;

		$args = array( 'exclude_from_search' => false );
		$choices = apply_filters( 'cfs_field_relationship_post_types', get_post_types( $args ) );

		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e('Post Types', 'cfs'); ?></label>
				<p class="description"><?php _e('Limit posts to the following types', 'cfs'); ?></p>
			</td>
			<td>
				<?php
					CFS()->create_field( array(
							'type'          => 'select',
							'input_name'    => "cfs[fields][$key][options][post_types]",
							'options'       => array( 'multiple' => '1', 'choices' => $choices ),
							'value'         => $this->get_option( $field, 'post_types' ),
						));
				?>
			</td>
		</tr>
	<?php
	}


	function prepare_value( $value, $field = null ) {
		return $value;
	}


	function format_value_for_input( $value, $field = null ) {
		return empty( $value ) ? '' : implode( ',', $value );
	}


	function format_value_for_api( $value, $field = null ) {
		if ( ! empty( $value ) && is_array( $value ) && isset( $value[0] ) ) {
			$value = absint( $value[0] );
		} else {
			$value = 0;
		}
		return $value;
	}


	function pre_save( $value, $field = null ) {
		if ( !empty( $value ) ) {
			// Inside a loop, the value is $value[0]
			$value = (array) $value;

			// The raw input saves a comma-separated string
			if ( false !== strpos( $value[0], ',' ) ) {
				return explode( ',', $value[0] );
			}

			return $value;
		}

		return array();
	}
}
