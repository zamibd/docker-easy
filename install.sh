#!/bin/bash

# Strict error handling
set -euo pipefail
IFS=$'\n\t'

# Colors
GREEN="\e[32m"
RED="\e[31m"
YELLOW="\e[33m"
BLUE="\e[34m"
RESET="\e[0m"

prompt_install() {
  local name=$1
  local install_command=$2
  local version_command=$3

  echo -e "${YELLOW}🔧 Do you want to install latest ${name}? [y/N]${RESET}"
  read -r answer
  if [[ "$answer" =~ ^[Yy]$ ]]; then
    echo -e "${BLUE}➡️ Installing ${name}...${RESET}"
    eval "$install_command"
    echo -e "${GREEN}✅ ${name} installed. Version:${RESET}"
    eval "$version_command"
  else
    echo -e "${RED}⏭️ Skipping ${name} installation.${RESET}"
  fi
}

# -----------------------------
# Update package index
# -----------------------------
sudo apt update -y
sudo apt install -y curl ca-certificates gnupg lsb-release software-properties-common

# -----------------------------
# Tool Install Prompts
# -----------------------------

# Docker
prompt_install "Docker" \
  "curl -fsSL https://get.docker.com | sh" \
  "docker --version"

# Docker Compose (latest release from GitHub)
prompt_install "Docker Compose" \
  "sudo curl -SL https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose && sudo chmod +x /usr/local/bin/docker-compose" \
  "docker-compose --version || docker compose version"

# Git (from PPA to get latest)
prompt_install "Git" \
  "sudo add-apt-repository ppa:git-core/ppa -y && sudo apt update && sudo apt install -y git" \
  "git --version"

# Terraform (official latest)
prompt_install "Terraform" \
  "curl -fsSL https://apt.releases.hashicorp.com/gpg | sudo gpg --dearmor -o /usr/share/keyrings/hashicorp-archive-keyring.gpg && echo \"deb [signed-by=/usr/share/keyrings/hashicorp-archive-keyring.gpg] https://apt.releases.hashicorp.com \$(lsb_release -cs) main\" | sudo tee /etc/apt/sources.list.d/hashicorp.list && sudo apt update && sudo apt install -y terraform" \
  "terraform version"

# Fail2Ban (APT-based; mostly up-to-date)
prompt_install "Fail2Ban" \
  "sudo apt install -y fail2ban && sudo systemctl enable fail2ban && sudo systemctl start fail2ban" \
  "fail2ban-client -V | head -n 1"

# -----------------------------
# App Import + Deploy
# -----------------------------

echo -e "${YELLOW}📥 Importing project from Git repository...${RESET}"

read -rp "🔗 Enter Git repository URL: " REPO_URL
if [[ -z "$REPO_URL" ]]; then
  echo -e "${RED}❌ Git repo URL cannot be empty.${RESET}"
  exit 1
fi

REPO_NAME=$(basename "$REPO_URL" .git)
if [[ -d "$REPO_NAME" ]]; then
  echo -e "${RED}⚠️ Directory '$REPO_NAME' already exists. Please remove it first.${RESET}"
  exit 1
fi

echo -e "${GREEN}🔄 Cloning repository...${RESET}"
git clone "$REPO_URL"


echo -e "${GREEN}📂 Copying files to './app'...${RESET}"
cp -a "$REPO_NAME"/. ./app/
rm -rf "$REPO_NAME"

echo -e "${GREEN}🔐 Fixing './app' permissions...${RESET}"
chown -R "$(id -u):$(id -g)" ./app
chmod -R 755 ./app

echo -e "${YELLOW}🐳 Restarting Docker containers...${RESET}"
docker compose down || docker-compose down
docker compose up -d --build || docker-compose up -d --build

echo -e "${RED}🗑️ Deleting installer script (install.sh)...${RESET}"
rm -- "$0"

echo -e "${GREEN}✅ Done! Project deployed and install.sh deleted.${RESET}"