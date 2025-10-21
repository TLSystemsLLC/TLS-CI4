SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spTeam_Get]
(@TeamKey INT)
AS
/* Get a single team by TeamKey */
SELECT
    TeamKey,
    TeamName,
    TeamPhone,
    TeamFax,
    ReportGroup,
    AgentKey,
    AgentPay,
    CompanyID,
    DivisionID
FROM tTeam
WHERE TeamKey = @TeamKey;

IF @@ROWCOUNT = 0
    RETURN 99;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spTeam_Get] TO [FullUser]
GO
