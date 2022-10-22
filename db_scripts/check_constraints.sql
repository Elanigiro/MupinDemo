/*mupin_login*/

ALTER TABLE `user`
  ADD CONSTRAINT `user_simple_email_check` CHECK (`email` regexp '^\\w+(?:\\.\\w+)*@\\w+(?:\\.\\w+)+$');

/*mupin*/
ALTER TABLE `author`
  ADD CONSTRAINT `author_name_check` CHECK (!(`first_name` regexp '^\\s*$') and !(`last_name` regexp '^\\s*$'));

ALTER TABLE `book`
  ADD CONSTRAINT `book_isbn_check` CHECK (`isbn` regexp '^[0-9]{13,13}$'),
  ADD CONSTRAINT `book_title_check` CHECK (!(`title` regexp '^\\s*$')),
  ADD CONSTRAINT `book_year_check` CHECK (`year` <> 0),
  ADD CONSTRAINT `book_pages_check` CHECK (`pages` > 0);

ALTER TABLE `company`
  ADD CONSTRAINT `company_name_check` CHECK (!(`company_name` regexp '^\\s*$'));

ALTER TABLE `computer`
  ADD CONSTRAINT `computer_model_check` CHECK (!(`model` regexp '^\\s*$')),
  ADD CONSTRAINT `computer_numcols1_check` CHECK (`clock_hz` regexp '^[0-9]+$' and `ram_byte` regexp '^[0-9]+$'),
  ADD CONSTRAINT `computer_numcols2_check` CHECK (`storage_byte` regexp '^[0-9]+$'),
  ADD CONSTRAINT `computer_year_check` CHECK (`year` <> 0);

ALTER TABLE `cpu`
  ADD CONSTRAINT `cpu_name_check` CHECK (!(`cpu_name` regexp '^\\s*$'));

ALTER TABLE `inventory`
  ADD CONSTRAINT `inv_id_check` CHECK (`inv_id` regexp '^[A-Z0-9]{20,20}$');

ALTER TABLE `magazine`
  ADD CONSTRAINT `mag_title_check` CHECK (!(`title` regexp '^\\s*$')),
  ADD CONSTRAINT `magazine_magno_year_check` CHECK (`year` <> 0 and `mag_no` > 0);

ALTER TABLE `os`
  ADD CONSTRAINT `os_name_version_check` CHECK (!(`os_name` regexp '^\\s*$') and !(`os_version` regexp '^\\s*$'));

ALTER TABLE `peripheral`
  ADD CONSTRAINT `peripheral_model_check` CHECK (!(`model` regexp '^\\s*$'));

ALTER TABLE `peripheraltype`
  ADD CONSTRAINT `pt_name_check` CHECK (!(`type_name` regexp '^\\s*$'));

ALTER TABLE `publisher`
  ADD CONSTRAINT `publisher_name_check` CHECK (!(`pub_name` regexp '^\\s*$'));

ALTER TABLE `software`
  ADD CONSTRAINT `software_title_sd_check` CHECK (!(`title` regexp '^\\s*$') and !(`storage_device` regexp '^\\s*$'));

ALTER TABLE `softwaretype`
  ADD CONSTRAINT `st_name_check` CHECK (!(`type_name` regexp '^\\s*$'));

ALTER TABLE `tag`
  ADD CONSTRAINT `tag_string_check` CHECK (`tag_string` regexp '^\\b[ \'\\w]*\\b$' and !(`tag_string` regexp '^\\s*$'));