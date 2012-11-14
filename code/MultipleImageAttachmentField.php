<?php

/**
 * MultipleImageAttachmentField
 */
class MultipleImageAttachmentField extends MultipleFileAttachmentField {

	/**
	 * Saves the form data into a record. Nulls out all of the existing file relationships
	 * and rebuilds them, in order to accommodate any deletions.
	 *
	 * @param DataObject $record The record associated with the parent form
	 * 
	 * @See Modified for pull request by Micah Sheets to add manymanysortable
	 */
	public function saveInto(DataObject $record) {
		// Can't do has_many without a parent id
		if(!$record->isInDB()) {
			$record->write();
		}
		if(!$file_class = $this->getFileClass($record)) {
			return false;
		}
		// Null out all the existing relations and reset.
		$currentComponentSet = $record->{$this->name}();
		$currentComponentSet->removeAll();

		if(isset($_REQUEST[$this->name]) && is_array($_REQUEST[$this->name])) {
			if($relation_name = $this->getForeignRelationName($record)) {
				// Assign all the new relations (may have already existed)
				$data = $_REQUEST;
				for($count = 0; $count < count($data[$this->name]); ++$count) {
					$id = $data[$this->name][$count];
					$sort = $data['sort'][$count];
					if($file = DataObject::get_by_id("File", $id)) {
						if (!($file instanceof Image)) {
							continue;
						}
						$new = ($file_class != "File") ? $file->newClassInstance($file_class) : $file;
						$new->write();
						$currentComponentSet->add($new, array('ManyManySort'=>$sort));
					}
				}
			}
		}		
	}

}