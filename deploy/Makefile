RELEASE_NAME ?= p4-styleguide

CHART_NAME ?= p4/static

SHELL := /bin/bash

APP_SOURCE := /home/circleci/app/deploy/docker/source

NAMESPACE ?= default

DEV_CLUSTER ?= p4-development
DEV_PROJECT ?= planet-4-151612
DEV_ZONE ?= us-central1-a

PROD_CLUSTER ?= planet4-production
PROD_PROJECT ?= planet4-production
PROD_ZONE ?= us-central1-a

SED_MATCH ?= [^a-zA-Z0-9._-]
ifeq ($(CIRCLECI),true)
# Configure build variables based on CircleCI environment vars
BUILD_NUM = build-$(CIRCLE_BUILD_NUM)
BRANCH_NAME ?= $(shell sed 's/$(SED_MATCH)/-/g' <<< "$(CIRCLE_BRANCH)")
BUILD_TAG ?= $(shell sed 's/$(SED_MATCH)/-/g' <<< "$(CIRCLE_TAG)")
else
# Not in CircleCI environment, try to set sane defaults
BUILD_NUM = build-local
BRANCH_NAME ?= $(shell git rev-parse --abbrev-ref HEAD | sed 's/$(SED_MATCH)/-/g')
BUILD_TAG ?= $(shell git tag -l --points-at HEAD | tail -n1 | sed 's/$(SED_MATCH)/-/g')
endif



# If BUILD_TAG is blank there's no tag on this commit
ifeq ($(strip $(BUILD_TAG)),)
# Default to branch name
BUILD_TAG := $(BRANCH_NAME)
else
# Consider this the new :latest image
PUSH_LATEST := true
endif

REVISION_TAG = $(shell git rev-parse --short HEAD)


lint:
	# yamllint .circleci/config.yml
	yamllint values.yaml
	yamllint env/dev/values.yaml
	yamllint env/prod/values.yaml

pull:
	docker pull gcr.io/planet-4-151612/openresty:latest

checkout:
	rsync -av /home/circleci/app/ $(APP_SOURCE)/ --exclude=deploy

checkout-master: checkout
	echo $${CIRCLE_SHA1:0:8} > $(APP_SOURCE)/version.txt
	yq -r .hostname /home/circleci/app/deploy/env/dev/values.yaml | tr -d '\n' > $(APP_SOURCE)/hostname.txt
	yq -r .hostpath /home/circleci/app/deploy/env/dev/values.yaml | tr -d '\n' > $(APP_SOURCE)/hostpath.txt

checkout-tag: checkout
	echo ${CIRCLE_TAG} > $(APP_SOURCE)/version.txt
	yq -r .hostname /home/circleci/app/deploy/env/prod/values.yaml | tr -d '\n' > $(APP_SOURCE)/hostname.txt
	yq -r .hostpath /home/circleci/app/deploy/env/prod/values.yaml | tr -d '\n' > $(APP_SOURCE)/hostpath.txt

docker/public:
	cd docker/source ; npm install
	sudo npm install -g gulp-cli
	cd docker/source ; gulp build
	cp -R docker/source/dist docker/public

build: docker/public

.PHONY: test-a11y
test-a11y: install-pa11y
	cd docker/source ; gulp & sleep 3
	cd docker/source ; gulp test

.PHONY: install-pa11y
install-pa11y: install-puppeteer-deps
	npm install pa11y-ci pa11y-ci-reporter-html

.PHONY: install-puppeteer-deps
install-puppeteer-deps:
	sudo apt-get update && sudo apt-get install -yq gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 \
	libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 \
	libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 \
	libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
	ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget

docker-build: lint pull docker/public echo-build
	docker build --tag=greenpeaceinternational/p4-styleguide:$(BUILD_NUM) docker

docker-push:
	docker push greenpeaceinternational/p4-styleguide:$(BUILD_NUM)

dev-config:
	gcloud config set project $(DEV_PROJECT)
	gcloud container clusters get-credentials $(DEV_CLUSTER) --zone $(DEV_ZONE) --project $(DEV_PROJECT)

prod-config:
	gcloud config set project $(PROD_PROJECT)
	gcloud container clusters get-credentials $(PROD_CLUSTER) --zone $(PROD_ZONE) --project $(PROD_PROJECT)

echo-build:
	$(info BUILD_NUM is [${BUILD_NUM}])

dev: lint dev-config
	helm init --client-only
	helm repo add p4 https://planet4-helm-charts.storage.googleapis.com && \
	helm repo update
	helm upgrade --install --force --recreate-pods --wait $(RELEASE_NAME) $(CHART_NAME) \
		--namespace=$(NAMESPACE) \
		--set image.tag=$(BUILD_NUM) \
		--values values.yaml \
		--values env/dev/values.yaml

prod: lint prod-config
	helm init --client-only
	helm repo add p4 https://planet4-helm-charts.storage.googleapis.com && \
	helm repo update
	helm upgrade --install --force --recreate-pods --wait $(RELEASE_NAME) $(CHART_NAME) \
		--namespace=$(NAMESPACE) \
		--set image.tag=$(BUILD_NUM) \
		--values values.yaml \
		--values env/prod/values.yaml
