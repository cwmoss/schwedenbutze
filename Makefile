all: build_static deploy_static

build_static:
	echo "Build static page"
	cd slft;./vendor/bin/slowfoot build -f

deploy_static:
	echo "Deploy static pages"
	rsync -av slft/dist/ uoeh.de:/var/www/apps/schwedenbutze/static

css:
	echo "Building css"
	cd slft/src/assets/css;sassc index.scss > index.css