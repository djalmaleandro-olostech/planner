resource "aws_iam_role" "codepipeline_role" {
  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action : "sts:AssumeRole",
        Principal : {
          Service : "codepipeline.amazonaws.com"
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

resource "aws_iam_role_policy" "codepipeline_policy" {
  role = aws_iam_role.codepipeline_role.id
  policy = templatefile("./policies/codepipeline_policy.json", {
    aws_s3_bucket_arn       = data.aws_s3_bucket.codepipeline.arn
    aws_region              = local.region
    account_id              = data.aws_caller_identity.current.account_id
    codestar_connection_arn = data.aws_codestarconnections_connection.github_connection.arn
  })

  lifecycle {
    create_before_destroy = false
  }
}

resource "aws_codepipeline" "codepipeline" {
  name     = local.project_name
  role_arn = aws_iam_role.codepipeline_role.arn

  artifact_store {
    location = data.aws_s3_bucket.codepipeline.bucket
    type     = "S3"
  }

  stage {
    name = "Source"

    action {
      name             = "GetAppSourceCodeAtGitHub"
      category         = "Source"
      owner            = "AWS"
      provider         = "CodeStarSourceConnection"
      version          = "1"
      output_artifacts = ["source"]
      configuration = {
        ConnectionArn        = data.aws_codestarconnections_connection.github_connection.arn
        FullRepositoryId     = "${local.repository.organization}/${local.repository.name}"
        BranchName           = local.repository.branch
        OutputArtifactFormat = "CODEBUILD_CLONE_REF"
      }
    }
  }

  stage {
    name = "Build"

    action {
      name             = "GetAppSecrets"
      category         = "Invoke"
      owner            = "AWS"
      provider         = "Lambda"
      version          = "1"
      output_artifacts = ["env"]
      run_order        = 1
      configuration = {
        FunctionName   = "get-env"
        UserParameters = "{ \"SecretName\": \"${local.environment}/env/${local.base_name}\", \"RegionName\": \"${local.region}\" }"
      }
    }

    action {
      name             = "Dockers"
      category         = "Build"
      owner            = "AWS"
      provider         = "CodeBuild"
      version          = "1"
      input_artifacts  = ["source", "env"]
      output_artifacts = ["imagedefinitions"]
      run_order        = 2
      configuration = {
        PrimarySource = "source"
        ProjectName   = aws_codebuild_project.codebuild.name
      }
    }
  }

  stage {
    name = "Deploy"

    action {
      name            = "Webserver"
      category        = "Deploy"
      owner           = "AWS"
      provider        = "ECS"
      input_artifacts = ["imagedefinitions"]
      version         = "1"

      configuration = {
        ClusterName = local.ecs_cluster_name
        ServiceName = local.ecs_service_name
        FileName    = "imagedefinitions.json"
      }
    }

    action {
      name            = "Cron"
      category        = "Deploy"
      owner           = "AWS"
      provider        = "ECS"
      input_artifacts = ["imagedefinitions"]
      version         = "1"

      configuration = {
        ClusterName = local.ecs_cluster_name
        ServiceName = local.cron_ecs_service_name
        FileName    = "imagedefinitions.json"
      }
    }

  }

  tags     = local.tags
  tags_all = local.tags
}