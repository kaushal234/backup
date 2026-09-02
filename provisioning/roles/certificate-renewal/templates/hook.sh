#!/usr/bin/env bash

set -e
set -u
set -o pipefail

export PROVIDER_UPDATE_DELAY=${PROVIDER_UPDATE_DELAY:-"30"}
export PROVIDER=${PROVIDER:-"gandi"}

function deploy_challenge {
    local chain=($@)
    for ((i=0; i < $#; i+=3)); do
        local DOMAIN="${chain[i]}" TOKEN_FILENAME="${chain[i+1]}" TOKEN_VALUE="${chain[i+2]}"

        echo "deploy_challenge called: ${DOMAIN}, ${TOKEN_FILENAME}, ${TOKEN_VALUE}"

        lexicon $PROVIDER create ${DOMAIN} TXT --name="_acme-challenge.${DOMAIN}." --content="${TOKEN_VALUE}"
    done

    local DELAY_COUNTDOWN=$PROVIDER_UPDATE_DELAY
    while [ $DELAY_COUNTDOWN -gt 0 ]; do
        echo -ne "${DELAY_COUNTDOWN}\033[0K\r"
        sleep 1
        : $((DELAY_COUNTDOWN--))
    done
}

function clean_challenge {
    local chain=($@)
    for ((i=0; i < $#; i+=3)); do
        local DOMAIN="${chain[i]}" TOKEN_FILENAME="${chain[i+1]}" TOKEN_VALUE="${chain[i+2]}"

        echo "clean_challenge called: ${DOMAIN}, ${TOKEN_FILENAME}, ${TOKEN_VALUE}"

        lexicon $PROVIDER delete ${DOMAIN} TXT --name="_acme-challenge.${DOMAIN}." \
        --content="${TOKEN_VALUE}"
    done
}

function invalid_challenge() {
    local DOMAIN="${1}" RESPONSE="${2}"

    echo "invalid_challenge called: ${DOMAIN}, ${RESPONSE}"
}

function deploy_cert {
    local DOMAIN="${1}" KEYFILE="${2}" CERTFILE="${3}" FULLCHAINFILE="${4}" CHAINFILE="${5}" TIMESTAMP="${6}"

    echo " + Creating combined.pem (a combined privkey.pem + cert.pem)"
    cd "$(dirname "${CERTFILE}")" && {
        cat "${FULLCHAINFILE}" "${KEYFILE}" > "combined-${TIMESTAMP}.pem" && \
        ln -sf "combined-${TIMESTAMP}.pem" "combined.pem" && {
            for filename in "combined-"*".pem"; do
              if [[ ! "${filename}" = "combined-${TIMESTAMP}.pem" ]]; then
                echo " + Removing unused combined certificate file: ${filename}"
                rm "${filename}"
              fi
            done
        }
    }

    echo "deploy_cert called: ${DOMAIN}, ${KEYFILE}, ${CERTFILE}, ${FULLCHAINFILE}, ${CHAINFILE}"
}

function unchanged_cert {
    local DOMAIN="${1}" KEYFILE="${2}" CERTFILE="${3}" FULLCHAINFILE="${4}" CHAINFILE="${5}"

    echo "unchanged_cert called: ${DOMAIN}, ${KEYFILE}, ${CERTFILE}, ${FULLCHAINFILE}, ${CHAINFILE}"
}

exit_hook() {
  :
}

startup_hook() {
  :
}

HANDLER=$1; shift;
if [ -n "$(type -t $HANDLER)" ] && [ "$(type -t $HANDLER)" = function ]; then
  $HANDLER "$@"
fi
