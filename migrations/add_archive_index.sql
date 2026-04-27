ALTER TABLE `posts`
  ADD KEY `idx_posts_archive` (`company_id`, `status`, `published_date`);
