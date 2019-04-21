FROM composer
RUN composer global require "laravel/installer"
ENV PATH $PATH:/tmp/vendor/bin