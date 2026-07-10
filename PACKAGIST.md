# Packagist Release Checklist

1. Push the `polytext` repository to GitHub (public).
2. Create and push tag `v1.0.0`.
3. Register the package at [packagist.org](https://packagist.org) with the GitHub repository URL.
4. Enable the Packagist GitHub webhook for automatic updates on new tags.
5. In `bargardon.dev`, remove the local `repositories` path entry and run:

```bash
composer require alirashidnahal/polytext:^1.0
```
