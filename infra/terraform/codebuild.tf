resource "aws_iam_role" "codebuild_role" {
  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action : "sts:AssumeRole",
        Principal : {
          Service : "codebuild.amazonaws.com"
        },
        Effect : "Allow"
      }
    ]
  })
  force_detach_policies = true
  lifecycle {
    create_before_destroy = false
  }
  tags     = local.tags
  tags_all = local.tags
}

resource "aws_iam_role_policy" "codebuild_policy" {
  role = aws_iam_role.codebuild_role.id
  policy = templatefile("./policies/codebuild_policy.json", {
    aws_s3_bucket_arn       = data.aws_s3_bucket.codepipeline.arn
    aws_region              = local.region
    account_id              = data.aws_caller_identity.current.account_id
    codestar_connection_arn = data.aws_codestarconnections_connection.github_connection.arn
  })

  lifecycle {
    create_before_destroy = false
  }
}

resource "aws_codebuild_project" "codebuild" {
  name          = local.project_name
  build_timeout = "480"
  service_role  = aws_iam_role.codebuild_role.arn

  artifacts {
    type = "CODEPIPELINE"
  }

  environment {
    compute_type    = "BUILD_GENERAL1_MEDIUM"
    image           = "aws/codebuild/standard:7.0"
    type            = "LINUX_CONTAINER"
    privileged_mode = true

    environment_variable {
      name  = "ENVIRONMENT_NAME"
      value = local.environment
    }

    environment_variable {
      name  = "APP_NAME"
      value = local.project_name
    }

    environment_variable {
      name  = "NEW_RELIC_LICENSE_KEY"
      value = data.aws_ssm_parameter.newrelic-license.value
    }

    environment_variable {
      name  = "AWS_REGION"
      value = local.region
    }

    environment_variable {
      name  = "AWS_ACCOUNT_ID"
      value = data.aws_caller_identity.current.id
    }

    environment_variable {
      name  = "APP_URL"
      value = aws_route53_record.this.fqdn
    }

    environment_variable {
      name  = "REPOSITORY_URI"
      value = aws_ecr_repository.ecr-php-service.repository_url
    }

  }

  vpc_config {
    security_group_ids = [data.aws_security_group.default-sg.id]
    subnets            = data.aws_subnets.default-subnets.ids
    vpc_id             = data.aws_vpc.default-vpc.id
  }

  source {
    type      = "CODEPIPELINE"
    buildspec = "buildspec.yaml"
  }
  tags     = local.tags
  tags_all = local.tags
}