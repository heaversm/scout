<?php
class FormSchema {
	public static $excluded_select_attributes = array(
		'value',
		'empty_default',
		'multi_select'
	);

	private static $input_fields = array(
		'general' => array(
			'name' => array(
				'required' => true,
				'title' => 'Please enter the name',
			),
		)
	);

	private static $select_fields = array(
		'general' => array(
			'category_id' => array(
				'required' => true,
				'title' => 'Please select the project category',
				'options' => array('Category', 'getCategoriesFormData'),
			),
			'second_category_id' => array(
				'required' => true,
				'title' => 'Please select the project category',
				'options' => array('Category', 'getCategoriesFormData'),
			),
			'third_category_id' => array(
				'required' => true,
				'title' => 'Please select the project category',
				'options' => array('Category', 'getCategoriesFormData'),
			),
			'fourth_category_id' => array(
				'required' => true,
				'title' => 'Please select the project category',
				'options' => array('Category', 'getCategoriesFormData'),
			),
			'fifth_category_id' => array(
				'required' => true,
				'title' => 'Please select the project category',
				'options' => array('Category', 'getCategoriesFormData'),
			),
			'sixth_category_id' => array(
				'required' => true,
				'title' => 'Please select the project category',
				'options' => array('Category', 'getCategoriesFormData'),
			),
			'link_category_id' => array(
				'required' => true,
				'title' => 'Please select the project category',
				'options' => array('LinkCategory', 'getLinkCategoriesFormData'),
			),
		)
	);

	private static $textarea_fields = array(
		'general' => array(
			'description' => array(
				'required' => true,
				'cols' => 4,
				'rows' => 4
			),
			'credits' => array(
				'required' => true,
				'cols' => 4,
				'rows' => 4
			),
            'embed_code' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed1' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed2' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed3' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed4' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed5' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed6' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed7' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed8' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed9' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed10' => array(
                'cols' => 4,
                'rows' => 4
            ),
            'video_embed' => array(
                'cols' => 4,
                'rows' => 4
            ),
			'body' => array(
				'cols' => 4,
				'rows' => 4
			),
			'social' => array(
				'cols' => 4,
				'rows' => 4
			),
			'awards' => array(
				'cols' => 4,
				'rows' => 4
			),
			'contact' => array(
				'cols' => 4,
				'rows' => 4
			),
			'clients' => array(
				'cols' => 4,
				'rows' => 4
			),
		)
	);

	private static $file_fields = array(
		'general' => array(
			'cover_fileid' => array(
				'title' => 'Please select the project\'s cover image',
			),
			'large_fileid' => array(
				'title' => 'Please select the project\'s assets',
			) ,
            'hero_fileid' => array(
                'title' => 'Please select the project\'s hero',
            )
		)
	);

	public static function getSchemaProperties($collection, $field) {
		foreach(array('textarea_fields', 'select_fields', 'input_fields', 'file_fields') as $type) {
			$field_type = self::$$type;

			if(isset($field_type[$collection]) && isset($field_type[$collection][$field])) {
				return $field_type[$collection][$field];
			} if(isset($field_type['general'][$field])) {
				return $field_type['general'][$field];
			}
		}

		return array();
	}

	public static function getSchemaType($collection, $field) {
		foreach(array('textarea_fields', 'select_fields', 'input_fields', 'file_fields') as $type) {
			$field_type = self::$$type;

			if((isset($field_type[$collection]) && isset($field_type[$collection][$field])) || isset($field_type['general'][$field])) {
				return $type;
			}
		}

		return 'input';
	}
}