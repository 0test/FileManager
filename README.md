# FileManager
 File manager for web users Evolution CMS


CREATE TABLE `{PREFIX}userfiles` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `user_filename` text NOT NULL,
  `real_filename` text NOT NULL,
  `viewer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
