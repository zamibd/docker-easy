
### Direct clone from github

# Clone git repo:

git clone https://github.com/zamibd/docker-easy.git

# Go To project

cd docker-easy

# edit domain name 

nano docker/nginx/default.conf

# Edit .env.example  to .env
nano  .env

# Edit Redis config
nano docker/redis/redis.conf

# install docker+compose+git and your github project url:
docker-easy
bash install.sh

==============================================================================


### setup from Local computer

# 1 Open terminal and clone the repo

git clone https://github.com/zamibd/docker-easy.git


# 2 Go To project

cd docker-easy/terraform

# 3 Edit terraform.tfvars 
# need Linode token and ssh password

nano terraform.tfvars

# start terraform
terraform init

terraform plan

terraform apply
