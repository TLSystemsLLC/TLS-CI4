SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spTeams_GetByDivision]
(
    @CompanyID INT,
    @DivisionID INT
)
AS
/* Get all teams for a division */
SELECT
    TeamKey,
    TeamName,
    TeamPhone,
    TeamFax,
    ReportGroup,
    AgentKey,
    AgentPay
FROM tTeam
WHERE CompanyID = @CompanyID
  AND DivisionID = @DivisionID
ORDER BY TeamName;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spTeams_GetByDivision] TO [FullUser]
GO
