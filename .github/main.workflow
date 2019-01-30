workflow "Main Workflow" {
  on = "push"
  resolves = ["PHPStan"]
}

action "PHPStan" {
  uses = "docker://oskarstark/phpstan-ga"
  secrets = ["GITHUB_TOKEN"]
  args = "analyse --level=max src/"
}
