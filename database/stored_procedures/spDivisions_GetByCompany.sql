SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spDivisions_GetByCompany]
(
    @CompanyID INT,
    @IncludeInactive BIT = 0
)
AS
/* Get all divisions for a company */
SELECT
    CompanyID,
    DivisionID,
    Name,
    City,
    State,
    Active
FROM tDivision
WHERE CompanyID = @CompanyID
  AND (@IncludeInactive = 1 OR Active = 1)
ORDER BY Name;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spDivisions_GetByCompany] TO [FullUser]
GO
