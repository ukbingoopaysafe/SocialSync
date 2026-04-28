ALTER TABLE `activity_log`
  MODIFY `post_id` int(11) DEFAULT NULL;

ALTER TABLE `notifications`
  MODIFY `post_id` int(11) DEFAULT NULL;
