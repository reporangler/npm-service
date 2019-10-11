#!/usr/bin/env sh

# Configure XDebug
XDEBUG_INI=/usr/local/etc/php/conf.d/xdebug.ini

if [[ "${XDEBUG_ENABLE}" == "1" ]]; then
    echo "Enabling XDebug Configuration"
    # we use tilde ~ here instead of / because of path names
    sed -i "s~__XDEBUG_IDE_KEY__~${XDEBUG_IDE_KEY}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_ENABLE__~${XDEBUG_ENABLE}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_AUTOSTART__~${XDEBUG_AUTOSTART}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_HOST__~${XDEBUG_HOST}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_PORT__~${XDEBUG_PORT}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_LOG_DIR__~${XDEBUG_LOG_DIR}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_PROFILER_ENABLE__~${XDEBUG_PROFILER_ENABLE}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_PROFILER_ENABLE_TRIGGER__~${XDEBUG_PROFILER_ENABLE_TRIGGER}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_PROFILER_ENABLE_TRIGGER_VALUE__~${XDEBUG_PROFILER_ENABLE_TRIGGER_VALUE}~g" ${XDEBUG_INI}
    sed -i "s~__XDEBUG_PROFILER_OUTPUT_DIR__~${XDEBUG_PROFILER_OUTPUT_DIR}~g" ${XDEBUG_INI}

    echo "" > ${XDEBUG_LOG_DIR}/xdebug.log
    chown www-data:www-data ${XDEBUG_LOG_DIR}/xdebug.log
    chmod 775 ${XDEBUG_LOG_DIR}/xdebug.log

    # we need write permissions to write the cachegrind files that the profiler generates
    if [[ "${XDEBUG_PROFILER_ENABLE_TRIGGER}" == "1" ]] || [[ "${XDEBUG_PROFILER_ENABLE}" == "1" ]]; then
        chown www-data:www-data ${XDEBUG_PROFILER_OUTPUT_DIR}
        chmod 775 ${XDEBUG_PROFILER_OUTPUT_DIR}
    fi
else
    echo "Disabling XDebug Configuration"
    rm -f ${XDEBUG_INI}
    rm -f ${XDEBUG_LOG_DIR}/xdebug.log
fi
