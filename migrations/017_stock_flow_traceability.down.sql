-- Roll back application code first. Do not remove warehouse master entries with history.
-- No automatic destructive down migration: links identify posted transactions, and
-- reverting qty precision can irreversibly round real stock. Restore the pre-017 backup
-- only after reviewing subsequent postings; otherwise keep these additive columns.
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Manual rollback required: preserve posted stock links and 3-decimal quantities';
