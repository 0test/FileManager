# FileManager
 File manager for web users Evolution CMS
- DEV!
```
CREATE TABLE `{PREFIX}userfiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `user_filename` text NOT NULL,
  `real_filename` text NOT NULL,
  `file_description` text NOT NULL,
  `viewer_id` int(11) NOT NULL,
  `upload_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `files_group_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

```
