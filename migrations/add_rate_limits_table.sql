-- Security Migration: Add rate_limits table
-- Run this migration to enable rate limiting

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL COMMENT 'IP or action:IP composite key',
  `action` varchar(50) NOT NULL COMMENT 'Action type (login, api, upload)',
  `attempts` int(11) DEFAULT 1,
  `first_attempt` timestamp DEFAULT CURRENT_TIMESTAMP,
  `last_attempt` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_identifier_action` (`identifier`, `action`),
  KEY `idx_cleanup` (`last_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
