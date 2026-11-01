# Security policy

## Supported versions

The maintainer releases security fixes for the latest published version only.

| Version        | Supported |
| -------------- | --------- |
| Latest release | Yes       |
| Older releases | No        |

Upgrade to the latest release before reporting a vulnerability that may already be fixed.

## Reporting a vulnerability

Do not report suspected vulnerabilities in a public issue, discussion, or pull request.

Email [developermadan+github@gmail.com](mailto:developermadan+github@gmail.com) with the subject
`[SECURITY] remotemerge/esewa-php-sdk`. Include:

- the affected package version, PHP version, and eSewa environment (test or production);
- a description of the vulnerability and its security impact;
- the affected input and conditions needed to reproduce it;
- the smallest bounded reproduction available; and
- any suggested mitigation or fix, if you have one.

Do not send merchant secret keys, production credentials, real transaction data, or personal data.
Use the public eSewa test credentials in reproductions.

Vulnerabilities in eSewa's own services, accounts, or merchant dashboard are outside the scope of
this project. Report those to [eSewa](https://esewa.com.np) directly.

## What to expect

The maintainer will confirm receipt, assess the report, and keep you informed as the investigation
progresses. There is no guaranteed response time. The maintainer prioritizes confirmed
vulnerabilities by severity and by the work needed to release a safe fix.

Please keep the report private until a fix and affected-version guidance are available, or a
disclosure date is agreed. The maintainer will coordinate public disclosure and credit with the
reporter. You may remain anonymous or decline credit.