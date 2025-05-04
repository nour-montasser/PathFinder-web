# PathFinder-web

## Google Sign-In Configuration

To enable Google Sign-In, follow these steps:

1. Install the required packages:
```bash
composer require knpuniversity/oauth2-client-bundle
composer require league/oauth2-google
```

2. Configure Google OAuth credentials:
   - Go to the [Google Developer Console](https://console.developers.google.com/)
   - Create a new project
   - Enable the Google+ API
   - Create OAuth credentials (Web application type)
   - Set the authorized redirect URI to: `https://your-domain.com/connect/google/check`
   - Copy the Client ID and Client Secret

3. Update your `.env` file with these values:
```
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
```

4. Update your `config/packages/knpu_oauth2_client.yaml` file with the Google configuration