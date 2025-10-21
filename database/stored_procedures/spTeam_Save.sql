SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spTeam_Save]
(
    @TeamKey INT,
    @TeamName VARCHAR(20),
    @TeamPhone VARCHAR(50) = NULL,
    @TeamFax VARCHAR(50) = NULL,
    @ReportGroup CHAR(20) = NULL,
    @AgentKey INT = NULL,
    @AgentPay DECIMAL(18, 5) = NULL,
    @CompanyID INT = NULL,
    @DivisionID INT = NULL
)
AS
/* Save (insert or update) a team */

-- Validate that parent division exists (if provided)
IF @CompanyID IS NOT NULL AND @DivisionID IS NOT NULL
BEGIN
    IF NOT EXISTS (SELECT 1 FROM tDivision WHERE CompanyID = @CompanyID AND DivisionID = @DivisionID)
    BEGIN
        RETURN 98; -- Invalid parent
    END
END

-- Check if team exists
SELECT @TeamKey = TeamKey
FROM tTeam
WHERE TeamKey = @TeamKey;

IF @@ROWCOUNT = 0
BEGIN
    -- INSERT new team
    INSERT INTO tTeam
    (
        TeamKey,
        TeamName,
        TeamPhone,
        TeamFax,
        ReportGroup,
        AgentKey,
        AgentPay,
        CompanyID,
        DivisionID
    )
    VALUES
    (
        @TeamKey,
        @TeamName,
        @TeamPhone,
        @TeamFax,
        @ReportGroup,
        @AgentKey,
        @AgentPay,
        @CompanyID,
        @DivisionID
    );

    IF @@ROWCOUNT = 0
        RETURN 97;
END
ELSE
BEGIN
    -- UPDATE existing team
    UPDATE tTeam
    SET
        TeamName = @TeamName,
        TeamPhone = @TeamPhone,
        TeamFax = @TeamFax,
        ReportGroup = @ReportGroup,
        AgentKey = @AgentKey,
        AgentPay = @AgentPay,
        CompanyID = @CompanyID,
        DivisionID = @DivisionID
    WHERE TeamKey = @TeamKey;

    IF @@ROWCOUNT = 0
        RETURN 97;
END

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spTeam_Save] TO [FullUser]
GO
