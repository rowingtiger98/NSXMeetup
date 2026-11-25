-- -------------------------------------------------------------------------------------
-- My Account > Events You've Registered For
--
-- Links registrations (nsx_meetup_participants) to the account that manages them, so a
-- user sees - and can edit - only their own. New registrations made while logged in (or
-- while creating an account) are linked automatically once the column exists; steps 2-4
-- link the registrations made before this change.
--
-- Run step 1 first. Steps 2-4 are optional and can be re-run safely.
-- -------------------------------------------------------------------------------------

-- 1. The column (until it exists the site carries on as before and the tab says it isn't available yet)
ALTER TABLE nsx_meetup_participants
  ADD COLUMN Participant_User_ID INT(6) NULL DEFAULT NULL,
  ADD INDEX idx_participant_user_id (Participant_User_ID);


-- 2. PREVIEW: earlier registrations whose email address matches exactly one account's
--    email address. Look these over before running step 3.
SELECT p.Participant_ID, p.Event_ID, p.Participant_Name, p.Participant_Email,
       a.NSX_User_ID, a.NSX_Username, a.NSX_Full_Name
FROM nsx_meetup_participants p
JOIN nsx_meetup_user_accounts a ON LOWER(TRIM(a.NSX_Email_Address)) = LOWER(TRIM(p.Participant_Email))
WHERE p.Participant_User_ID IS NULL
  AND (SELECT COUNT(*) FROM nsx_meetup_user_accounts a2
       WHERE LOWER(TRIM(a2.NSX_Email_Address)) = LOWER(TRIM(p.Participant_Email))) = 1
ORDER BY a.NSX_User_ID, p.Participant_ID;


-- 3. Link them.
UPDATE nsx_meetup_participants p
JOIN nsx_meetup_user_accounts a ON LOWER(TRIM(a.NSX_Email_Address)) = LOWER(TRIM(p.Participant_Email))
SET p.Participant_User_ID = a.NSX_User_ID
WHERE p.Participant_User_ID IS NULL
  AND (SELECT COUNT(*) FROM nsx_meetup_user_accounts a2
       WHERE LOWER(TRIM(a2.NSX_Email_Address)) = LOWER(TRIM(p.Participant_Email))) = 1;


-- 4. The second person on a registration belongs to whoever registered them, as new
--    registrations do. The second person is saved straight after driver one, whose
--    Traveling_With then reads "<name> as a passenger". Preview with:
--      SELECT g.Participant_ID, g.Participant_Name, d.Participant_Name AS registered_by, d.Participant_User_ID
--      FROM nsx_meetup_participants g JOIN nsx_meetup_participants d ON g.Participant_ID = d.Participant_ID + 1
--      WHERE d.Participant_User_ID IS NOT NULL AND d.Participant_Traveling_With LIKE '% as a passenger';
UPDATE nsx_meetup_participants g
JOIN nsx_meetup_participants d ON g.Participant_ID = d.Participant_ID + 1
SET g.Participant_User_ID = d.Participant_User_ID
WHERE d.Participant_User_ID IS NOT NULL
  AND d.Participant_Traveling_With LIKE '% as a passenger';
