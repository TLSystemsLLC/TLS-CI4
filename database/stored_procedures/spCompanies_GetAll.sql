SET QUOTED_IDENTIFIER ON
GO
SET ANSI_NULLS ON
GO

CREATE PROCEDURE [dbo].[spCompanies_GetAll]
(@IncludeInactive BIT = 0)
AS
/* Get all companies for grid display */
SELECT
    CompanyID,
    CompanyName,
    ShortName,
    SCAC,
    MainPhone,
    Active
FROM tCompany
WHERE (@IncludeInactive = 1 OR Active = 1)
ORDER BY CompanyName;

RETURN 0;

GO
GRANT EXECUTE ON [dbo].[spCompanies_GetAll] TO [FullUser]
GO
