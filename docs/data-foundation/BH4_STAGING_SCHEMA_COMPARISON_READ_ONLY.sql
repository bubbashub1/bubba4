-- BH4 staging schema comparison evidence: metadata only, no customer rows.
-- Confirm DATABASE() is the intended STAGING database before running.
-- All statements are SELECT; no schema or data modifications.
SELECT DATABASE() AS selected_database, @@version AS mysql_version;
SELECT TABLE_NAME, TABLE_TYPE, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND (TABLE_NAME LIKE '%activities' OR TABLE_NAME LIKE '%venues'
       OR TABLE_NAME LIKE '%organisers' OR TABLE_NAME LIKE '%sessions'
       OR TABLE_NAME LIKE '%activity_venues'
       OR TABLE_NAME LIKE '%bh3_booking%'
       OR TABLE_NAME LIKE '%bh_children' OR TABLE_NAME LIKE '%bh_bumps'
       OR TABLE_NAME LIKE '%bh_saved_activities'
       OR TABLE_NAME LIKE '%bh_planner'
       OR TABLE_NAME LIKE '%bh_user_preferences')
ORDER BY TABLE_NAME;
SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_TYPE,
       IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (TABLE_NAME LIKE '%activities' OR TABLE_NAME LIKE '%venues'
       OR TABLE_NAME LIKE '%organisers' OR TABLE_NAME LIKE '%sessions'
       OR TABLE_NAME LIKE '%activity_venues'
       OR TABLE_NAME LIKE '%bh3_booking%'
       OR TABLE_NAME LIKE '%bh_children' OR TABLE_NAME LIKE '%bh_bumps'
       OR TABLE_NAME LIKE '%bh_saved_activities'
       OR TABLE_NAME LIKE '%bh_planner'
       OR TABLE_NAME LIKE '%bh_user_preferences')
ORDER BY TABLE_NAME, ORDINAL_POSITION;
SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME,
       INDEX_TYPE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND (TABLE_NAME LIKE '%activities' OR TABLE_NAME LIKE '%venues'
       OR TABLE_NAME LIKE '%organisers' OR TABLE_NAME LIKE '%sessions'
       OR TABLE_NAME LIKE '%activity_venues'
       OR TABLE_NAME LIKE '%bh3_booking%'
       OR TABLE_NAME LIKE '%bh_children' OR TABLE_NAME LIKE '%bh_bumps'
       OR TABLE_NAME LIKE '%bh_saved_activities'
       OR TABLE_NAME LIKE '%bh_planner'
       OR TABLE_NAME LIKE '%bh_user_preferences')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
-- Record output as schema-only evidence; do not export user records.
