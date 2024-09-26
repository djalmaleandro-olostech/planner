data "aws_vpc" "default-vpc" {
  tags = {
    Name = local.vpc_name
  }
}

data "aws_subnets" "default-subnets" {
  filter {
    name   = "tag:Name"
    values = local.subnet_names
  }
}

data "aws_subnets" "public-subnets" {
  filter {
    name   = "tag:Name"
    values = local.public_subnet_names
  }
}

data "aws_security_group" "default-sg" {
  name   = "default"
  vpc_id = data.aws_vpc.default-vpc.id
}

data "aws_route53_zone" "public" {
  name = local.route53_record_name
}

data "aws_s3_bucket" "codepipeline" {
  bucket = "${local.environment}-gcnet-codepipeline-default"
}

data "aws_ssm_parameter" "vpn_endpoint" {
  name = "/${local.environment}/VPN_ORIGIN_CIDR_BLOCK"
}

data "aws_ssm_parameter" "newrelic-license" {
  name = "/newrelic/license-key"
}

data "aws_codestarconnections_connection" "github_connection" {
  name = "tf-default"
}