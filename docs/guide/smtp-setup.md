# SMTP Setup

::: danger Important
Without an SMTP plugin, WordPress sends emails through your hosting server's `mail()` function. Most hosts (OVH, Infomaniak, o2switch, GoDaddy, etc.) block or spam-filter these emails. **Your quote notifications will not arrive.**
:::

## Recommended: FluentSMTP (Free)

1. Go to **Plugins > Add New**
2. Search for **"FluentSMTP"**
3. Click **Install Now** then **Activate**
4. Go to **Settings > FluentSMTP**
5. Choose your email provider:

### Gmail / Google Workspace

- Connection: **Gmail API**
- Follow the OAuth setup wizard
- Test with the "Send Test Email" button

### Brevo (ex-Sendinblue) -- Free 300 emails/day

- Connection: **SMTP**
- Host: `smtp-relay.brevo.com`
- Port: `587`
- Username: your Brevo email
- Password: your Brevo SMTP key

### Mailgun / SendGrid

- Connection: **SMTP** or dedicated API option
- Follow FluentSMTP's built-in wizard

## Test Your Setup

After configuring SMTP:

1. Go to **FluentSMTP > Email Test**
2. Send a test email to yourself
3. Check your inbox (not spam folder)
4. If received -- you're ready to use LiteQuote

## Troubleshooting

| Problem | Solution |
|---|---|
| Emails go to spam | Configure SPF and DKIM records on your domain |
| Emails not sent at all | Check FluentSMTP logs in Settings > FluentSMTP > Email Logs |
| Gmail connection fails | Make sure "Less secure apps" is enabled or use OAuth |
| Timeout errors | Try port 465 (SSL) instead of 587 (TLS) |
