# TYPO3 Extension: ImgProxy

Use [imgproxy](https://github.com/imgproxy/imgproxy) to render images asynchronously and serve them in new 
formats like webp and avif when the client supports them.

Basically TYPO3 now just renders a processing url and imgproxy serves the resulting image. The image processing is 
really fast compared to GraphicsMagick and ImageMagick and less memory hungry.

It does not store thumbnail informations in `sys_file_processedfile` nor save generated images locally, so for production usage a caching proxy is highly recommended.

## Installation

`composer require christophlehmann/imgproxy`

## Configuration

* `improxyUrl` is the url of imgproxy.
* `key` & `salt` are used for signing the urls. Generate them with `echo $(xxd -g 2 -l 64 -p /dev/random | tr -d '\n')`.
* `helperUrl` can be used to tell imgproxy how it reaches the source image. Handy in development environment: Set it to the projects live url and you don't need the images locally.
* `allowedExtensions` List of file extensions that should be handled with imgproxy
* `formatQuality` Can be used to define different compressions for avif,webp,.. Default: empty (TYPO3s quality setting is used). Example: `jpeg:70:avif:40:webp:60` 

## Run imgproxy locally with docker

1. Set `imgproxyUrl` to `http://localhost:8080`
2. Set `helperUrl` to `local:///` when the docker container can't reach your local webserver
3. Start imgproxy

```shell
docker run \
  --env IMGPROXY_KEY=*yourkey* \
  --env IMGPROXY_SALT=*yoursalt* \
  --env IMGPROXY_ENABLE_WEBP_DETECTION=true \
  --env IMGPROXY_ENABLE_AVIF_DETECTION=true \
  --env IMGPROXY_LOCAL_FILESYSTEM_ROOT=/data \
  --volume /path/to/documentroot:/data \
  --publish 127.0.0.1:8080:8080 \
  -it darthsim/imgproxy
```
