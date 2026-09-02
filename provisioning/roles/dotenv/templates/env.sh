if [ -r {{ dotenv_path }} ]; then
  set -a
  . {{ dotenv_path }}
  set +a
fi