# Authentication Testing Guide

## Prerequisites

1. ✅ MAMP is running (Apache + PHP 8.3.14)
2. ✅ Files copied to `/Applications/MAMP/htdocs/tls-ci4/`
3. ✅ SQL Server accessible at 35.226.40.170:1433
4. ✅ Test databases available: DEMO, TLSYS

## Test Credentials

### DEMO Database Users
- **User:** chodge, cknox, cscott, dhatfield, egarcia
- **Password:** [Your password for these users]

### TLSYS Database Users
- **User:** SYSTEM, tlyle, wjohnston
- **Password:** [Your password for these users]

---

## Test Scenario 1: Basic Authentication Flow

### Test 1.1: Access Protected Page (Unauthenticated)

**What to do:**
1. Open your web browser
2. Navigate to: http://localhost:8888/tls-ci4/dashboard

**Expected Result:**
- ✅ You should be **automatically redirected** to: http://localhost:8888/tls-ci4/login
- ✅ You should see the login form with TLS branding (truck icon, green theme)
- ✅ Form has three fields: Customer, User ID, Password

**What this tests:**
- Authentication filter is working
- Redirect to login works
- Protected routes cannot be accessed without login

---

### Test 1.2: Direct Login Page Access

**What to do:**
1. Navigate to: http://localhost:8888/tls-ci4/login

**Expected Result:**
- ✅ Login form displays with TLS theme
- ✅ Page title: "Login - TLS Operations"
- ✅ Three input fields visible:
  - Customer (database name)
  - User ID
  - Password
- ✅ "Sign In" button at bottom

**What this tests:**
- Login route is accessible
- View renders correctly with TLS theme

---

### Test 1.3: Invalid Customer Login Attempt

**What to do:**
1. On login page, enter:
   - **Customer:** `master` (invalid - protected database)
   - **User ID:** `tlyle`
   - **Password:** [any password]
2. Click "Sign In"

**Expected Result:**
- ✅ Page reloads with error message
- ✅ Error displays: "Invalid customer ID specified"
- ✅ Form fields retain their values (except password)

**What this tests:**
- Customer database validation against spGetOperationsDB
- Protection against accessing system databases
- Error handling and display

---

### Test 1.4: Invalid Credentials

**What to do:**
1. On login page, enter:
   - **Customer:** `DEMO`
   - **User ID:** `invaliduser`
   - **Password:** `wrongpassword`
2. Click "Sign In"

**Expected Result:**
- ✅ Page reloads with error message
- ✅ Error displays: "Invalid username or password"
- ✅ Customer and User ID fields retain values
- ✅ Password field is empty (for security)

**What this tests:**
- spUser_Login stored procedure validation
- Credential verification in customer database
- Security: password not retained in form

---

### Test 1.5: Successful Login to DEMO Database

**What to do:**
1. On login page, enter:
   - **Customer:** `DEMO`
   - **User ID:** `tlyle` (or another valid DEMO user)
   - **Password:** [correct password]
2. Click "Sign In"

**Expected Result:**
- ✅ Redirect to: http://localhost:8888/tls-ci4/dashboard
- ✅ Dashboard displays with TLS theme
- ✅ User Information card shows:
  - User ID: tlyle
  - Name: [User's full name from DEMO database]
  - Customer Database: DEMO
  - Company: [Company name from DEMO database]
  - Session Start: [Current timestamp]
- ✅ Company Information card shows DEMO company details
- ✅ Permissions card shows menu items user has access to
- ✅ "Logout" button visible in top-right

**What this tests:**
- Complete authentication flow
- Database switching to DEMO
- spUser_Login, spUser_Menus, spUser_GetUser, spCompany_Get execution
- Session creation and storage
- Dashboard rendering with user data

---

### Test 1.6: Session Persistence

**What to do:**
1. While logged in, navigate to: http://localhost:8888/tls-ci4/dashboard
2. Refresh the page (F5 or Cmd+R)
3. Open a new tab and navigate to: http://localhost:8888/tls-ci4/dashboard

**Expected Result:**
- ✅ Dashboard displays immediately (no redirect to login)
- ✅ Same user information shown
- ✅ Session data persists across page refreshes
- ✅ Session works in new browser tabs

**What this tests:**
- CI4 session persistence
- isLoggedIn() validation
- Session data retention

---

### Test 1.7: Logout

**What to do:**
1. While logged in to dashboard, click the "Logout" button

**Expected Result:**
- ✅ Redirect to: http://localhost:8888/tls-ci4/login
- ✅ Success message displays: "You have been logged out successfully"
- ✅ Session is destroyed

**What this tests:**
- Logout functionality
- Session destruction
- Redirect after logout
- Flash message display

---

### Test 1.8: Access After Logout

**What to do:**
1. After logging out, try to navigate to: http://localhost:8888/tls-ci4/dashboard

**Expected Result:**
- ✅ Redirect to: http://localhost:8888/tls-ci4/login
- ✅ Cannot access protected page without re-authentication

**What this tests:**
- Session is truly destroyed
- Authentication required after logout

---

## Test Scenario 2: Multi-Tenant Database Isolation

### Test 2.1: Login to DEMO Database

**What to do:**
1. Login with:
   - **Customer:** `DEMO`
   - **User ID:** `tlyle`
   - **Password:** [correct password]

**Expected Result:**
- ✅ Dashboard shows DEMO database information
- ✅ Customer Database field shows: **DEMO**
- ✅ Company name from DEMO database
- ✅ Permissions specific to DEMO database

**Record the following for comparison:**
- Company name: _______________
- Number of permissions: _______________
- Sample menu items: _______________

---

### Test 2.2: Logout and Login to TLSYS Database

**What to do:**
1. Click "Logout"
2. Login with:
   - **Customer:** `TLSYS`
   - **User ID:** `tlyle`
   - **Password:** [correct password]

**Expected Result:**
- ✅ Dashboard shows TLSYS database information
- ✅ Customer Database field shows: **TLSYS**
- ✅ **DIFFERENT** company name (compared to DEMO)
- ✅ **DIFFERENT** permissions (compared to DEMO)

**Compare with DEMO:**
- Company name should be DIFFERENT: _______________
- Number of permissions may be DIFFERENT: _______________
- Menu items may be DIFFERENT: _______________

**What this tests:**
- Multi-tenant database switching
- Each customer database has independent data
- Session stores correct customer database
- No cross-tenant data leakage

---

### Test 2.3: Verify Database Context in Session

**What to do:**
1. Login to DEMO database
2. On dashboard, look at the User Information card

**Expected Result:**
- ✅ Customer Database field shows: **DEMO**
- ✅ All data is from DEMO database

**Then:**
1. Logout
2. Login to TLSYS database
3. On dashboard, look at the User Information card

**Expected Result:**
- ✅ Customer Database field shows: **TLSYS**
- ✅ All data is from TLSYS database

**What this tests:**
- Session correctly stores customer_db
- Database context switches properly
- Each login is isolated to its tenant database

---

## Test Scenario 3: Form Validation

### Test 3.1: Empty Form Submission

**What to do:**
1. Navigate to login page
2. Leave all fields empty
3. Click "Sign In"

**Expected Result:**
- ✅ Validation errors display:
  - "Customer ID is required"
  - "User ID is required"
  - "Password is required"
- ✅ Form does not submit
- ✅ Red validation styling on fields

**What this tests:**
- CI4 form validation rules
- Required field validation
- Error display

---

### Test 3.2: Invalid Characters in Customer Field

**What to do:**
1. Enter:
   - **Customer:** `DEMO'; DROP TABLE Users;--` (SQL injection attempt)
   - **User ID:** `tlyle`
   - **Password:** [any password]
2. Click "Sign In"

**Expected Result:**
- ✅ Validation error: "Customer ID contains invalid characters"
- ✅ Form rejects input
- ✅ No database query is executed

**What this tests:**
- Input sanitization
- Protection against SQL injection
- alpha_numeric_punct validation rule

---

### Test 3.3: Customer Field Max Length

**What to do:**
1. Enter:
   - **Customer:** [Type 60+ characters]
   - **User ID:** `tlyle`
   - **Password:** [any password]
2. Click "Sign In"

**Expected Result:**
- ✅ Validation error: "Customer ID is too long"
- ✅ Max length validation enforced (50 characters)

**What this tests:**
- Max length validation
- Protection against buffer overflow attacks

---

## Test Scenario 4: Browser Compatibility

### Test 4.1: Different Browsers

**What to do:**
Test login flow in multiple browsers:
- Chrome/Edge
- Firefox
- Safari

**Expected Result:**
- ✅ Login works in all browsers
- ✅ TLS theme renders correctly
- ✅ Bootstrap 5 compatibility
- ✅ Session management works

---

### Test 4.2: Mobile Responsive Design

**What to do:**
1. Open browser developer tools (F12)
2. Toggle device toolbar (mobile view)
3. Test login on mobile sizes:
   - iPhone (375px)
   - iPad (768px)

**Expected Result:**
- ✅ Login form is responsive
- ✅ Fields stack vertically on mobile
- ✅ Buttons are full-width on mobile
- ✅ Logo and branding display correctly

---

## Test Scenario 5: Security Testing

### Test 5.1: CSRF Protection

**What to do:**
1. View page source on login page (Cmd+U or Ctrl+U)
2. Look for hidden CSRF token field

**Expected Result:**
- ✅ Hidden input field present: `<input type="hidden" name="csrf_test_name" value="...">`
- ✅ Token is random/unique on each page load
- ✅ Form submission includes CSRF token

**What this tests:**
- CI4 CSRF protection enabled
- Forms are protected against cross-site request forgery

---

### Test 5.2: Session Timeout

**What to do:**
1. Login successfully
2. Wait for session timeout (check .env for SESSION_TIMEOUT value)
3. Try to access dashboard after timeout

**Expected Result:**
- ✅ Redirect to login page
- ✅ Session expired, must re-authenticate
- ✅ Message may indicate session timeout

**What this tests:**
- Session timeout enforcement
- Security: old sessions cannot be reused

---

### Test 5.3: Password Not Stored in Form

**What to do:**
1. Enter credentials and submit
2. If login fails, check the password field

**Expected Result:**
- ✅ Password field is **empty** after failed login
- ✅ Only customer and user_id retained
- ✅ Password never displayed in page source

**What this tests:**
- Password security
- Passwords not retained in form after submission

---

## Test Scenario 6: Error Logging and Debugging

### Test 6.1: Check Logs for Successful Login

**What to do:**
1. Login successfully with DEMO database
2. Check CI4 logs at: `/Applications/MAMP/htdocs/tls-ci4/writable/logs/`
3. Open the most recent log-YYYY-MM-DD.log file

**Expected Result:**
- ✅ Log entry present: `INFO --> Successful login: User 'tlyle' to database 'DEMO'`
- ✅ Timestamp matches login time

**What this tests:**
- Logging of successful authentication
- Audit trail for logins

---

### Test 6.2: Check Logs for Failed Login

**What to do:**
1. Attempt login with invalid credentials
2. Check CI4 logs

**Expected Result:**
- ✅ Log entry present: `WARNING --> Failed login attempt: User 'invaliduser' to database 'DEMO' - Code: ...`
- ✅ Failed attempts are logged

**What this tests:**
- Logging of failed authentication attempts
- Security audit trail

---

## Test Scenario 7: Direct Database Verification

### Test 7.1: Verify Stored Procedure Calls

**What to do:**
1. Login to DEMO database as tlyle
2. Dashboard should show menu permissions

**Verify in SQL Server:**
```sql
-- Connect to DEMO database
USE DEMO;

-- Verify user exists
EXEC spUser_Login 'tlyle', 'your_password';
-- Should return 0 for success

-- Verify menus
EXEC spUser_Menus 'tlyle';
-- Should return list of menu permissions

-- Verify user details
EXEC spUser_GetUser 'tlyle', NULL, NULL, NULL, NULL, NULL;
-- Should return user information

-- Verify company info
EXEC spCompany_Get 1;
-- Should return company details
```

**Expected Result:**
- ✅ All stored procedures execute without errors
- ✅ Data matches what's displayed on dashboard

**What this tests:**
- Stored procedure integration
- Data accuracy
- Database connectivity

---

## Troubleshooting Guide

### Problem: "Page Not Found" (404)

**Solution:**
1. Check MAMP is running
2. Verify URL: http://localhost:8888/tls-ci4/ (not /tls-ci4/public/)
3. Check .htaccess file exists in `/Applications/MAMP/htdocs/tls-ci4/public/`
4. Verify RewriteBase is set to `/tls-ci4/public/`

### Problem: "Cannot connect to database"

**Solution:**
1. Check SQL Server is accessible: `ping 35.226.40.170`
2. Verify .env file has correct database credentials
3. Check SQLSRV driver is installed: `php -m | grep sqlsrv`
4. Test connection from command line

### Problem: Login redirects to login (infinite loop)

**Solution:**
1. Check session directory is writable: `/Applications/MAMP/htdocs/tls-ci4/writable/session/`
2. Verify session configuration in .env
3. Clear browser cookies
4. Check CI4 logs for session errors

### Problem: "Invalid customer ID" for valid database

**Solution:**
1. Verify spGetOperationsDB exists in master database
2. Check database name is in the returned list
3. Verify database name is spelled correctly (case-sensitive)
4. Check database user has permission to master database

### Problem: Dashboard shows empty data

**Solution:**
1. Verify stored procedures exist in customer database
2. Check user has correct permissions in database
3. Review CI4 logs for stored procedure errors
4. Verify company record exists (CompanyKey = 1)

---

## Success Criteria

✅ **All tests should pass with these results:**

1. **Authentication Flow:**
   - Unauthenticated users redirected to login ✅
   - Invalid customers rejected ✅
   - Invalid credentials rejected ✅
   - Valid credentials authenticated ✅
   - Dashboard displays user data ✅
   - Logout destroys session ✅

2. **Multi-Tenant Isolation:**
   - DEMO database shows DEMO data ✅
   - TLSYS database shows TLSYS data ✅
   - No cross-tenant data leakage ✅
   - Session stores correct customer_db ✅

3. **Form Validation:**
   - Required fields enforced ✅
   - Invalid input rejected ✅
   - Max length enforced ✅

4. **Security:**
   - CSRF tokens present ✅
   - Session timeout enforced ✅
   - Passwords not retained ✅
   - Logging enabled ✅

5. **UI/UX:**
   - TLS theme applied ✅
   - Responsive design works ✅
   - Cross-browser compatible ✅

---

## Next Steps After Testing

Once all tests pass:

1. ✅ Mark "Test complete authentication flow" as complete
2. 📋 Proceed to Phase 3: MenuManager migration
3. 🚀 Begin building entity maintenance screens (Driver, Owner, etc.)

---

## Testing Checklist

Use this checklist to track your testing progress:

### Basic Authentication
- [ ] Test 1.1: Unauthenticated access redirect
- [ ] Test 1.2: Login page displays
- [ ] Test 1.3: Invalid customer rejected
- [ ] Test 1.4: Invalid credentials rejected
- [ ] Test 1.5: Successful DEMO login
- [ ] Test 1.6: Session persistence
- [ ] Test 1.7: Logout works
- [ ] Test 1.8: Access denied after logout

### Multi-Tenant Isolation
- [ ] Test 2.1: Login to DEMO
- [ ] Test 2.2: Login to TLSYS
- [ ] Test 2.3: Verify database context

### Form Validation
- [ ] Test 3.1: Empty form validation
- [ ] Test 3.2: Invalid characters rejected
- [ ] Test 3.3: Max length enforced

### Browser Compatibility
- [ ] Test 4.1: Multiple browsers
- [ ] Test 4.2: Mobile responsive

### Security
- [ ] Test 5.1: CSRF protection
- [ ] Test 5.2: Session timeout
- [ ] Test 5.3: Password not stored

### Logging
- [ ] Test 6.1: Successful login logged
- [ ] Test 6.2: Failed login logged

### Database Verification
- [ ] Test 7.1: Stored procedures work

---

**Ready to test?** Start with Test 1.1 and work through the scenarios sequentially!
