# WHMCS SW Hosting Registrar Module #

## Summary ##

The WHMCS SW Hosting Registrar Module integrates with your SW Panel account and allows you to automate domain provisioning and management such:

- Domain registrations
- Domain transfers
- Domain renewals
- Domain nameservers
- Domain contacts
- Domain lock and unlock
- Domain EPP code
- Domain availability
- Domain information sync
- Domain transfer sync

## Installation ##

1. Download the module
2. Upload modules/registrars/swhosting to your <WHMCS folder>/modules/registrars

## Configuration ##

1. Go to Setup > Products/Services > Domain registrars
2. Activate SW Hosting module
3. Set your Bearer Token and test mode as desired

Your Bearer Token is inside your SW Panel. Check for details https://www.swhosting.com/blog/ya-disponible-la-nueva-api-de-dominios/

## Configure TLD autoregistration ##

1. Go to Setup > Products/Services > Domain pricing
2. Select Swhosting in "Auto Registration" dropdown for the supported TLDs

## Choose Lookup Provider ##
1. Go to Setup > Products/Services > Domain pricing
2. Change Lookup provider and set "Domain registrar" and select SW Hosting
3. Configure Lookup Provider and select supported TLDs

You can view our current supported TLDs inside your SW Panel or at www.swhosting.com

## Troubleshooting ##
If there are any issue, or API commands are not working for some reason, the first troubleshooting step should be to look at the API logs.
1. Go to Utilities > Logs > Module Logs
2. Enable Debug Logging
3. You can find the raw API commands being sent and received by your WHMCS modules. The responses should contain some information about how the problem can be solved.

## Feedback ##
Contact us through Support Wall inside your SW Panel.

Feel free to expand and upgrade at your convenience. For more API information, please refer to the documentation at:
https://ote-api.swhosting.com/apidocs