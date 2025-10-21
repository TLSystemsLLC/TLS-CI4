SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spTeam_Delete]
(@TeamKey INT)
AS
/*
 * Delete a team
 * NOTE: tTeam has triggers (tD_tTeam and tU_tTeam) that will automatically
 * set TeamKey to NULL in tUser and tUnit when a team is deleted or updated.
 */

DELETE FROM tTeam
WHERE TeamKey = @TeamKey;

IF @@ROWCOUNT = 0
    RETURN 99;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spTeam_Delete] TO [FullUser]
GO
