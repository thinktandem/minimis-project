# Bootstrap 4.0.0

The built assets with popper.js for Drupal 8 composer install

To use in your theme, make stable your base theme and add this into your libraries yml

```yaml
bootstrap:
  version: 4.0.0
  css:
    component:
      ../../../libraries/bootstrap/css/bootstrap.min.css: {}
  js:
    ../../../libraries/bootstrap/js/bootstrap.min.js: {}
  dependencies:
    - core/jquery

popper:
  version: 1.12.9
  js:
    ../../../libraries/bootstrap/js/popper.min.js: {}
  dependencies:
    - core/jquery
```
