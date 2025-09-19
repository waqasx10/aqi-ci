import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
import time

def test_csv_upload():
    driver=webdriver.Chrome()
    driver.maximize_window()  
    driver.get("http://127.0.0.1:8000/") 
    
    file_input = driver.find_element(By.ID, "file-input")
    file_input.send_keys("/Users/waqas/Desktop/csvfile/contacts.csv")
    time.sleep(3)

    process_btn = driver.find_element(By.XPATH, "//button[normalize-space()='Process']")
    process_btn.click()
    time.sleep(5)
    
    success_msg = driver.find_element(By.XPATH, "//div[@class='mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800']]")
    assert success_msg.is_displayed()

    