# Spyimmo

Spyimmo is a real estate crawling Tool.

## Traefik - Generate your own certificates (Dev HTTPS usage)

Install mkcert
```
mkcert -install
```

Generate certificates
```
mkcert -cert-file infra/certs/local-cert.pem -key-file infra/certs/local-key.pem "docker.dev" "*.docker.dev"
```

## OAuth2

This project use Google account for login.

Configure your Google OAuth2 credentials, with a ``client_id`` and a ``client_secret``, provided in the [Google Developer console](https://console.developers.google.com/apis/credentials/oauthclient?project=raspberry-home-automation).
