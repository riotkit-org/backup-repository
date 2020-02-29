API/Project rules
=================

Releasing
=========

- Latest development version is always on `master`.
- Versions closer to the release date, but still under features development are released as ALPHA eg. 3.0.0-ALPHA1
- When all features are implemented for given version, but still some tests are corrected, smaller issues are being resolved we release a RC eg. 3.0.0-RC1
- When all automatic tests are passing, the RC was tested manually (by some tester, or used at local production for some time), then a stable is released eg. 3.0.0

Naming convention and formatting
================================

The response body in API requests follows a convention that:
- On first level in most of the responses there is a status code, error list returned in **snake_case format**
- On second level there are objects, their properties are **camelCase**
