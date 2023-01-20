
drupal cancel user deleting instead of blocking - this problem was caused by a form alter setting the method to delete, even though the configured defaulte was to block.

drupal 8 cancel selection form showing to people who do not have the permission to select method of cancellation - this may be the problem that caused previous developers to overspecify form alter overrides when hiding the form, and this is the haunted part.

