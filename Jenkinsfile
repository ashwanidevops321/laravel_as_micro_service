pipeline {
    agent any
}

environment {
    DOCKER_CREDENTIALS_ID = 'dockerhub-credentials'
    SSH_CREDENTIALS_ID = 'ssh-key-id-in-jenkins'
    SCRIPT              = 'docker-compose.yml'
    DOCKER_IMAGE_NAME =   'ashwanidevops321/lv_app'
    DEPLOY_PATH_2         = '/home/ubuntu/lv_app'
}

stages {
    stage('Checkout') {
        steps {
            checkout scm
        }
    }
    stage('Build Docker Image') {
        steps {
            script {
                docker.withRegistry('https://index.docker.io/v1/', DOCKER_CREDENTIALS_ID) {
                    def appImage = docker.build("ashwanidevops321/lv_app:${env.BUILD_ID}", "-f Dockerfile .")
                    appImage.push()
                }
            }
        }
    }

    stage('Deploy to Server') {
        steps {
            script {
                sshagent([SSH_CREDENTIALS_ID]) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} 'mkdir -p ${DEPLOY_PATH_2}'
                        scp ${SCRIPT} ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH_2}/docker-compose.yml
                        echo "🚀 Deploying containers on remote server..."
                            ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                                set -e
                                cd ${DEPLOY_PATH_2} &&
                                docker-compose pull &&
                                docker-compose down &&
                                docker-compose up -d --remove-orphans
                            '
                    """
                }
            }
        }
    }
}