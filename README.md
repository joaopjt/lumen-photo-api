# lumen-photo-api

An REST API to save photos and create albums being maded with [Lumen](https://lumen.laravel.com).

> Project for study reasons only.

## A custom Validation rule added

I've added one custom validation rule, called imageURL.
This rules makes a request (with [GuzzlePHP](http://docs.guzzlephp.org/) to the passed by URL and verify if the content-type
header has one of image MIME types.

**THIS IS TOTALLY SUPERFLUOUS, I'VE DONE THIS FOR STUDY REASON ONLY!**
