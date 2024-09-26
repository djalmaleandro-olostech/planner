provider "aws" {
  region = var.region

  default_tags {
    tags = {
      "map-migrated" : "mig40986"
      Terraform = "true"
    }
  }
}

data "aws_caller_identity" "current" {}

data "tfe_workspace" "workspace" {
  name         = "${local.base_name}-hyperf-${local.environment}"
  organization = "Domain"
}

locals {
  repository = {
    organization = split("/", data.tfe_workspace.workspace.vcs_repo[0].identifier)[0]
    name         = "ecidadao-api"
    branch       = local.environment == "prod" ? "main" : local.environment
  }
}