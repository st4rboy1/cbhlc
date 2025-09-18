# Laravel + Inertia.js Deployment Guide for DigitalOcean (2025)

## 📌 Overview

This guide covers modern deployment strategies for Laravel applications with React/Inertia.js on DigitalOcean, following security best practices for 2025.

## 🔐 Security Best Practices

### GitHub Deploy Keys vs Personal SSH Keys

**Deploy Keys** are the recommended approach for automated deployments:

- Scoped to a single repository (more secure)
- Not tied to a personal GitHub account
- Can be read-only or read-write
- Perfect for CI/CD automation

**Personal SSH Keys** should be avoided for automation because:

- They grant access to all repositories the user can access
- If compromised, they expose your entire GitHub account
- They tie deployments to individual team members

## 🚀 Deployment Options

### Option 1: DigitalOcean App Platform (Recommended for Teams)

**Pros:**

- Zero-configuration deployment
- Automatic GitHub integration
- Built-in SSL/HTTPS
- Auto-scaling capabilities
- No server management needed
- Perfect for student teams

**Setup:**

1. Fork the repository to your GitHub account
2. Go to [DigitalOcean App Platform](https://cloud.digitalocean.com/apps)
3. Click "Create App" → Select GitHub repository
4. App Platform auto-detects Laravel and configures everything
5. Set environment variables in the dashboard
6. Deploy! (Automatic re-deployments on git push)

**Pricing:** Starting at $5/month for basic tier

**Configuration:** Use the `.do/app.yaml` file included in this repository

### Option 2: Traditional Droplet with GitHub Actions

**Pros:**

- Full server control
- More cost-effective for multiple apps
- Better for complex configurations
- Educational value for learning DevOps

**Setup Process:**

#### Step 1: Create GitHub Deploy Key

```bash
# Generate a new Ed25519 key (more secure than RSA in 2025)
ssh-keygen -t ed25519 -C "deploy@cbhlc" -f ~/.ssh/cbhlc_deploy -N ""

# View the public key
cat ~/.ssh/cbhlc_deploy.pub
```

#### Step 2: Configure GitHub Repository

1. Go to Repository Settings → Deploy keys
2. Add new deploy key with the public key
3. Check "Allow write access" if needed for deployment logs

#### Step 3: Configure GitHub Secrets

Add these secrets to your repository (Settings → Secrets → Actions):

| Secret Name          | Description        | Value                             |
| -------------------- | ------------------ | --------------------------------- |
| `DEPLOY_PRIVATE_KEY` | Private deploy key | Contents of `~/.ssh/cbhlc_deploy` |
| `DEPLOY_HOST`        | Droplet IP address | Your DigitalOcean droplet IP      |
| `DEPLOY_USER`        | SSH username       | `deploy` or `forge`               |
| `DEPLOY_PATH`        | Application path   | `/var/www/cbhlc`                  |

#### Step 4: Server Setup

```bash
# On your DigitalOcean droplet
# Create deploy user
sudo adduser deploy
sudo usermod -aG www-data deploy

# Add deploy key to server
sudo su - deploy
mkdir -p ~/.ssh
echo "YOUR_PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Set up application directory
sudo mkdir -p /var/www/cbhlc
sudo chown deploy:www-data /var/www/cbhlc

# Clone repository
cd /var/www/cbhlc
git clone git@github.com:st4rboy1/cbhlc.git .
```

### Option 3: Laravel Forge (Managed Solution)

**Pros:**

- Server management included
- Automatic security updates
- Built-in deployment from GitHub
- Laravel-optimized configuration

**Setup:**

1. Connect Laravel Forge to DigitalOcean
2. Create a new server ($12/month + $5/month Forge subscription)
3. Add your site and connect GitHub repository
4. Enable quick deploy for automatic deployments

## 📊 Resource Requirements

### Minimum Server Specifications

- **RAM:** 1GB (build processes fail with less)
- **CPU:** 1 vCPU minimum
- **Storage:** 25GB SSD
- **PHP:** 8.2+ (8.4 recommended for Laravel 12)
- **Node.js:** 20+ (22 recommended)
- **MySQL:** 8.0+ (8.3 recommended)

## 🛠️ Deployment Workflow Files

### For App Platform

Use `.do/app.yaml` for configuration

### For GitHub Actions

Use `.github/workflows/deploy-secure.yml` for automated deployments

## 🔄 Deployment Process

The deployment pipeline follows these steps:

1. **Testing Phase**
    - Run PHP tests (Pest/PHPUnit)
    - Run frontend tests
    - Check code quality

2. **Build Phase**
    - Install Composer dependencies
    - Install NPM dependencies
    - Build frontend assets (Vite)

3. **Deployment Phase**
    - Enable maintenance mode
    - Pull latest code
    - Run migrations
    - Clear and rebuild caches
    - Restart services
    - Disable maintenance mode

## 🚨 Troubleshooting

### Common Issues

#### Build Stuck on `npm run build`

- **Cause:** Insufficient memory
- **Solution:** Upgrade to at least 1GB RAM

#### Permission Errors

```bash
# Fix storage permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Database Connection Issues

- Verify `.env` database credentials
- Check if MySQL service is running
- Ensure database exists

#### Asset Not Loading (Mixed Content)

```php
// In AppServiceProvider.php boot() method
if($this->app->environment('production')) {
    URL::forceScheme('https');
}
```

## 📈 Performance Optimization

### Caching Strategy

```bash
# After each deployment
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

### Inertia.js 2.0 Optimizations

- Enable link prefetching
- Use partial reloads
- Implement deferred prop loading
- Consider SSR for better SEO

## 🔍 Monitoring

### Application Health

- Set up health check endpoint at `/health`
- Monitor response times
- Track error rates

### Server Monitoring

```bash
# Check server resources
htop
df -h
free -m

# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx logs
tail -f /var/log/nginx/error.log
```

## 🎯 Quick Start Checklist

- [ ] Choose deployment method (App Platform vs Droplet)
- [ ] Generate deploy keys (if using Droplet)
- [ ] Configure GitHub secrets
- [ ] Set up environment variables
- [ ] Test deployment pipeline
- [ ] Configure domain and SSL
- [ ] Set up monitoring
- [ ] Create backup strategy

## 📚 Additional Resources

- [DigitalOcean App Platform Docs](https://docs.digitalocean.com/products/app-platform/)
- [Laravel Deployment Docs](https://laravel.com/docs/deployment)
- [Inertia.js Server-Side Setup](https://inertiajs.com/server-side-setup)
- [GitHub Deploy Keys Documentation](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/managing-deploy-keys)

## 🤝 Support

For deployment issues:

1. Check GitHub Actions logs for CI/CD failures
2. Review DigitalOcean dashboard for server issues
3. Consult Laravel and Inertia.js documentation
4. Ask for help in your team's communication channel

---

**Last Updated:** January 2025
**Maintained by:** CBHLC Development Team
