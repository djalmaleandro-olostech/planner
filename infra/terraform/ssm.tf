resource "aws_ssm_parameter" "region" {
  name  = "/${local.project_name}/env/AWS_DEFAULT_REGION"
  type  = "String"
  value = var.region

  tags     = local.tags
  tags_all = local.tags
}