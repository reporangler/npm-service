FROM nginx:stable-alpine

ARG BUILD_DATE="develop"
ARG VCS_REF="develop"

# The maintainer list
LABEL authors="Christopher Thomas <chris.thomas@antimatter-studios.com>"
LABEL org.label-schema.build-date=$BUILD_DATE
LABEL org.label-schema.vcs-ref=$VCS_REF

COPY docker/nginx/default.conf /etc/nginx/conf.d/
COPY public /www/public

WORKDIR /www

EXPOSE 80
