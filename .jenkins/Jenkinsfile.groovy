def unstable(def body) {
	catchError(stageResult: 'UNSTABLE', buildResult: 'UNSTABLE', catchInterruptions: false) {
		body()
	}
}

def runComposerTest(def version, def variant) {
	unstable {
		exec "composer update --prefer-${variant} --no-interaction --no-progress"
		exec "composer exec phpunit -- --log-junit .reports/${version}-${variant}.xml"
	}
}

def runLinter() {
	unstable {
        exec 'composer require phpstan/phpstan --no-interaction --no-progress'
        exec 'composer exec phpstan -- --no-progress --configuration=phpstan.neon.dist --error-format=junit > phpstan-report.xml'
	}
}

pipeline {
	agent none
	options {
		disableConcurrentBuilds()
		disableResume()
		disableRestartFromStage()
		skipDefaultCheckout()
	}
	environment {
		COMPOSER_PROCESS_TIMEOUT = '3600'
	}
	stages {
		stage('Lint') {
			agent {
				docker {
				    image 'faulo/farah:8.2'
					label 'docker'
                }
            }
			steps {
				checkout scm

                dir('.reports') {
                    deleteDir()
                }

				script {
					runLinter();
				}
			}
			post {
				always {
                    dir('.reports') {
                        junit "*.xml"
                    }
				}
			}
		}
		stage('Setup') {
			steps {
				script {
					def platforms = [ 'linux', 'windows' ]
					def versions = [ "8.2", "8.3", "8.4", "8.5" ]
					def variants = [ 'lowest', 'stable' ]

					def branches = [:]

					for (def p in platforms) {
						def platform = p
						for (def v in versions) {
							def version = v
							for (def a in variants) {
								def variant = a

								def name = "${platform} php-${version} ${variant}"
								def label = "${platform} && docker"
								def workspace = "php-${version}-${variant}"

								branches[name] = {
									stage(name) {
										node(label) {
											ws("${WORKSPACE}/${workspace}") {
												checkout scm

												dir('.reports') {
													deleteDir()
												}

												docker.image("faulo/farah:${version}").inside {
													runComposerTest(version, variant)
												}

												dir('.reports') {
													junit "*.xml"
												}
											}
										}
									}
								}
							}
						}
					}

					parallel branches
				}
			}
		}
	}
}
