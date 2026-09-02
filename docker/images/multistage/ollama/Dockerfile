FROM ollama/ollama

COPY docker/images/multistage/ollama/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]