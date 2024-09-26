resource "aws_codestarnotifications_notification_rule" "pipeline" {
  name        = "${local.base_name}-notification-rule"
  resource    = aws_codepipeline.codepipeline.arn
  detail_type = "FULL"

  event_type_ids = [
    "codepipeline-pipeline-pipeline-execution-started",
    "codepipeline-pipeline-pipeline-execution-failed",
    "codepipeline-pipeline-pipeline-execution-succeeded"
  ]

  target {
    type    = "AWSChatbotSlack"
    address = "arn:aws:chatbot::${data.aws_caller_identity.current.account_id}:chat-configuration/slack-channel/Deployments"
  }
}